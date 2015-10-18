<?php

namespace Bargency\Forms\Controls;

use Nette\Forms\Form,
	Nette\Forms\IControl,
	Nette\Forms\Controls\TextInput,
	Nette\Forms\Controls\TextBase,
	Nette\Utils\Strings,
	Nette\Application\Responses\JsonResponse;

/**
 * Tag Input for html forms
 *
 * @author Martin Adamek <adamek@bargency.com>
 * @copyright Royal Design s.r.o. 2012
 */
class TagInput extends TextInput
{

	/** @var string rule */
	const UNIQUE = ':unique';

	/** @var string rule Users cannot create new tags */
	const ORIGINAL = ':original';

	/** @var string */
	public $renderName;

	/** @var int */
	protected $payloadLimit = 5;

	/** @var string regex */
	protected $delimiter = '[\s,]+';

	/** @var string */
	protected $joiner = ', ';

	/** @var callback returning array */
	protected $suggestCallback;

	/**
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  maximum number of characters the user may enter
	 */
	public function __construct($label = NULL, $cols = NULL, $maxLength = NULL)
	{
		parent::__construct($label, $cols, $maxLength);
		$this->control->class[] = 'tags';
	}

	/**
	 * @param string $delimiter regex
	 * @return TagInput provides fluent interface
	 */
	public function setDelimiter($delimiter)
	{
		$this->delimiter = $delimiter;
		return $this;
	}

	/**
	 * @param string $delimiter
	 * @return TagInput provides fluent interface
	 */
	public function setJoiner($joiner)
	{
		$this->joiner = $joiner;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getValue()
	{
		// temporarily disable filters
		$filters = $this->filters;
		$this->filters = array();

		$res = Strings::split(parent::getValue(), "\x01" . $this->delimiter . "\x01");
		$this->filters = $filters;

		foreach ($res as &$tag) {
			foreach ($this->filters as $filter) {
				$tag = $filter($tag);
			}
			if (!$tag) {
				unset($tag);
			}
		}

		return $res;
	}

	/**
	 * Generates control's HTML element.
	 * @return \Nette\Utils\Html
	 */
	public function getControl()
	{
		/** @var $control \Nette\Utils\Html */
		$control = parent::getControl();

		$container = \Nette\Utils\Html::el('div')
										->id($this->getHtmlId() . '_tags')
										->class(array_merge((array) $control->attrs['class'], array('container')));

		if ($this->delimiter !== NULL && Strings::trim($this->delimiter) !== '') {
			$control->attrs['data-tag-delimiter'] = $this->delimiter;
		}

		if ($this->joiner !== NULL && Strings::trim($this->joiner) !== '') {
			$control->attrs['data-tag-joiner'] = $this->joiner;
		}

		$container->add($control);
		$component = '';
		$parent = $this->form->parent;
		while (!($parent instanceof \Nette\Application\IPresenter)) {
			$component .= $parent->name . '-';
			$parent = $parent->parent;
		}

		return $container;
	}

	/**
	 * Sets control's value.
	 *
	 * @param  string
	 * @return TextBase  provides a fluent interface
	 */
	public function setValue($value)
	{
		if (!is_array($value)) {
			$value = explode(trim($this->joiner), $value);
		}
		parent::setValue(implode($this->joiner, $value));
		return $this;
	}

	/**
	 * @param array $value
	 * @return TagInput provides fluent interface
	 */
	public function setDefaultValue($value)
	{
		if (!is_array($value)) {
			throw new \Nette\InvalidArgumentException("Invalid argument type passed to " . __METHOD__ . ", expected array.");
		}
		parent::setDefaultValue(implode($this->joiner, $value));
		return $this;
	}

	/**
	 * @param int $limit
	 * @return TagInput provides fluent interface
	 */
	public function setPayloadLimit($limit)
	{
		if ($limit < 0) {
			throw new \Nette\InvalidArgumentException("Invalid limit, expected positive integer.");
		}

		$this->payloadLimit = $limit;
		return $this;
	}

	/**
	 * Adds a validation rule.
	 * @param  mixed      rule type
	 * @param  string     message to display for invalid data
	 * @param  mixed      optional rule arguments
	 * @return FormControl  provides a fluent interface
	 */
	public function addRule($operation, $message = NULL, $arg = NULL)
	{
		switch($operation) {
			case Form::EQUAL:
				if (!is_array($arg)) {
					throw new \Nette\InvalidArgumentException(__METHOD__ . '(' . $operation . ') must be compared to array.');
				}
		}

		return parent::addRule($operation, $message, $arg);
	}

	/**
	 * @param \Nette\Application\UI\Presenter presenter
	 * @param string presenter
	 */
	public function renderResponse($presenter, $filter)
	{
		$data = array();
		if (!($this->suggestCallback instanceof \Nette\Callback)) {
			throw new \Nette\InvalidStateException('Callback not set.');
		}

		foreach ($this->suggestCallback->invoke($filter, $this->payloadLimit) as $tag) {
			if (count($data) >= $this->payloadLimit) {
				break;
			}
			$data[] = (string) $tag;
		}

		$presenter->sendResponse(new JsonResponse($data));
	}



	/********************* validation *********************/



	/**
	 * Equal validator: are control's value and second parameter equal?
	 * @param  IControl
	 * @param  mixed
	 * @return bool
	 */
	public static function validateEqual(IControl $control, $arg)
	{
		$value = $control->getValue();
		sort($value);
		sort($arg);
		return $value === $arg;
	}



	/**
	 * Filled validator: is control filled?
	 * @param  IControl
	 * @return bool
	 */
	public static function validateFilled(IControl $control)
	{
		return count($control->getValue()) !== 0;
	}



	/**
	 * Min-length validator: has control's value minimal length?
	 * @param  TextBase
	 * @param  int  length
	 * @return bool
	 */
	public static function validateMinLength(IControl $control, $length)
	{
		return count($control->getValue()) >= $length;
	}



	/**
	 * Max-length validator: is control's value length in limit?
	 * @param  TextBase
	 * @param  int  length
	 * @return bool
	 */
	public static function validateMaxLength(IControl $control, $length)
	{
		return count($control->getValue()) <= $length;
	}



	/**
	 * Length validator: is control's value length in range?
	 * @param  TextBase
	 * @param  array  min and max length pair
	 * @return bool
	 */
	public static function validateLength(IControl $control, $range)
	{
		if (!is_array($range)) {
			$range = array($range, $range);
		}
		$len = count($control->getValue());
		return ($range[0] === NULL || $len >= $range[0]) && ($range[1] === NULL || $len <= $range[1]);
	}



	/**
	 * Email validator: is control's value valid email address?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateEmail(TextBase $control)
	{
		throw new \LogicException(':EMAIL validator is not applicable to TagInput.');
	}



	/**
	 * URL validator: is control's value valid URL?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateUrl(TextBase $control)
	{
		throw new \LogicException(':URL validator is not applicable to TagInput.');
	}



	/** @deprecated */
	public static function validateRegexp(TextBase $control, $regexp)
	{
		throw new \LogicException(':REGEXP validator is not applicable to TagInput.');
	}



	/**
	 * Regular expression validator: matches control's value regular expression?
	 * @param  TextBase
	 * @param  string
	 * @return bool
	 */
	public static function validatePattern(TextBase $control, $pattern)
	{
		throw new \LogicException(':PATTERN validator is not applicable to TagInput.');
	}



	/**
	 * Integer validator: is each value of tag of control decimal number?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateInteger(TextBase $control)
	{
		foreach ($control->getValue() as $tag) {
			if (!Strings::match($tag, '/^-?[0-9]+$/')) {
				return FALSE;
			}
		}
		return TRUE;
	}



	/**
	 * Float validator: is each value of tag of control value float number?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateFloat(TextBase $control)
	{
		foreach ($control->getValue() as $tag) {
			if (!Strings::match($tag, '/^-?[0-9]*[.,]?[0-9]+$/')) {
				return FALSE;
			}
		}
		return TRUE;
	}



	/**
	 * Range validator: is a control's value number in specified range?
	 * @param  TextBase
	 * @param  array  min and max value pair
	 * @return bool
	 */
	public static function validateRange(IControl $control, $range)
	{
		throw new \LogicException(':RANGE validator is not applicable to TagInput.');
	}



	/**
	 * Uniqueness validator: is each value of tag of control unique?
	 * @param  TagInput
	 * @return bool
	 */
	public static function validateUnique(TagInput $control)
	{
		return count(array_unique($control->getValue())) === count($control->getValue());
	}



	/**
	 * Are all tags from suggest?
	 * @param  TagInput
	 * @return bool
	 */
	public static function validateOriginal(TagInput $control)
	{
		foreach ($control->getValue() as $tag) {
			$found = FALSE;
			foreach ($control->suggestCallback->invoke($tag, 1) as $suggest) {
				if ($tag === $suggest) return TRUE;
			}
			if (!$found) return FALSE;
		}
		return TRUE;
	}

}
