<?php

namespace B4nan\Forms\Controls;

use Nette\Application\Responses\JsonResponse;
use Nette\Forms\Controls\TextBase;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\InvalidArgumentException;
use Nette\Utils\Strings;

/**
 * Tag Input for html forms
 *
 * @author Martin Adamek <adamek@bargency.com>
 * @copyright B4nan s.r.o.
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
	protected $delimiter = '[\s;]+';

	/** @var string */
	protected $joiner = ';';

	/** @var callback returning array */
	protected $suggestCallback;

	/** @var bool */
	private $returnString;

	/**
	 * TagInput constructor.
	 * @param string $label
	 * @param bool $returnString
	 */
	public function __construct($label = NULL, $returnString = FALSE)
	{
		parent::__construct($label);
		$this->control->class[] = 'tags';
		$this->returnString = $returnString;
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
	 * @param string $joiner
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
		// temporarily disable rules
//		$rules = $this->rules;
//		$this->rules = array();
//
		$val = parent::getValue();

		if ($this->returnString) {
			return $val;
		}

		if (! $val) {
			return [];
		}

		$res = Strings::split($val, "\x01" . $this->delimiter . "\x01");
//		$this->rules = $rules;

		foreach ($res as & $tag) {
			foreach ($this->rules as $filter) {
				$tag = $filter($tag);
			}
			if (! $tag) {
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
			throw new InvalidArgumentException("Invalid argument type passed to " . __METHOD__ . ", expected array.");
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
			throw new InvalidArgumentException("Invalid limit, expected positive integer.");
		}

		$this->payloadLimit = $limit;
		return $this;
	}

	/**
	 * Adds a validation rule.
	 *
	 * @param string $operation
	 * @param null $message
	 * @param null $arg
	 * @return \Nette\Forms\Controls\BaseControl
	 */
	public function addRule($operation, $message = NULL, $arg = NULL)
	{
		switch ($operation) {
			case Form::EQUAL:
				if (! is_array($arg)) {
					throw new InvalidArgumentException(__METHOD__ . '(' . $operation . ') must be compared to array.');
				}
		}

		return parent::addRule($operation, $message, $arg);
	}

	/**
	 * @param \Nette\Application\UI\Presenter $presenter
	 * @param $filter
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
