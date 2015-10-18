<?php

namespace Bargency\Forms;

use Nette\Application\UI\Form as NForm,
	Nette\Forms\Rules;
use Nette\ComponentModel\IContainer;
use Nette\Forms\Validator;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class Form extends NForm
{

	/** @var int insert form */
	const TYPE_INSERT = 0;

	/** @var int update form */
	const TYPE_UPDATE = 1;

	/** @var int other form */
	const TYPE_OTHER = 2;

	/** @var bool toggle rendering of colon suffix in all labels */
	private static $renderColonSuffix = FALSE;

	/** @var bool toggle spam protection */
	private static $spamProtection = FALSE;

	/** @var bool toggle automatic CSRF protection */
	private static $useCsrfToken = TRUE;

	/**
	 * @param IContainer $parent
	 * @param string $name
	 */
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$this->setRenderer(new Renderer($this));

		if (self::$useCsrfToken) {
			$this->addProtection('The form has expired. Please re-submit.'); // CSRF protection
		}

		$this->monitor('Nette\Application\UI\Presenter');

		Validator::$messages[Controls\TagInput::UNIQUE] = 'Please insert each tag only once.';
		Validator::$messages[Controls\TagInput::ORIGINAL] = 'Please do use only suggested tags.';
		Validator::$messages[Controls\DateTimePicker::VALID] = 'Invalid date time';
	}

	/**
	 * removes CSRF protection token
	 */
	public function removeProtection()
	{
		unset($this[self::PROTECTOR_ID]);
	}

	/**
	 * @param \Nette\Application\IPresenter $presenter
	 */
	protected function attached($presenter)
	{
		parent::attached($presenter);
		$this->setTranslator($presenter->translator);
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @param array $items
	 * @param mixed $other
	 * @return Controls\EditableSelectBox
	 */
	public function addEditableSelect($name, $label = NULL, $items = NULL, $other = FALSE)
	{
		return $this[$name] = new Controls\EditableSelectBox($label, $items, $other);
	}

	/**
	 * @param  string  control name
	 * @param  string  caption
	 * @param  string  secret used for spam protection
	 * @return \Nette\Forms\Controls\SubmitButton
	 */
	public function addSubmit($name, $caption = NULL, $secret = 'nospam')
	{
		if (self::$spamProtection) {
//			$label = $this->translator->translate();
			$noSpam = $this->addText('nospam', ['Fill in "%s"', $secret])
						   ->addRule(Form::FILLED, 'You are a spambot!')
						   ->addRule(Form::EQUAL, 'You are a spambot!', $secret);

			$noSpam->labelPrototype->class('nospam');
			$noSpam->controlPrototype->class('nospam');
		}

		$control = parent::addSubmit($name, $caption);
		$control->controlPrototype->class[] = 'btn btn-medium';
		return $control;
	}

	/**
	 * @param  string  control name
	 * @param  string  caption
	 * @return \Nette\Forms\Controls\SubmitButton
	 */
	public function addButton($name, $caption = NULL)
	{
		$control = parent::addSubmit($name, $caption);
		$control->controlPrototype->class[] = 'btn btn-medium';
		return $control;
	}

	/**
	 * @param callable $callback
	 * @return \Nette\Forms\Controls\Button
	 */
	public function addCancel($callback = NULL)
	{
		if ($callback) {
			$control = parent::addSubmit('cancel', 'Cancel');
			$control->setValidationScope(FALSE);
			$control->onClick[] = $callback;
		} else {
			$control = parent::addButton('cancel', 'Cancel');
		}
		$control->controlPrototype->class[] = 'btn btn-medium';

		return $control;
	}

	/**
	 * Adds naming container to the form.
	 *
	 * @param string $name
	 * @return Container
	 */
	public function addContainer($name)
	{
		$control = new Container;
		$control->currentGroup = $this->currentGroup;
		return $this[$name] = $control;
	}

	/**
	 * @param Form $form
	 * @param string $name
	 * @param string $label
	 * @param array $suggest
	 * @return TagInput provides fluent interface
	 */
	public function addTag($name, $label = NULL)
	{
		$this[$name] = new Controls\TagInput($label);
		$this[$name]->renderName = 'tagInputSuggest' . ucfirst($name);
		return $this[$name];
	}

	/**
	 * add Redactor js
	 *
	 * @param string $name
	 * @param string $label
	 */
	public function addRedactor($name, $label = NULL)
	{
		return $this[$name] = new Controls\Redactor($label);
	}

	/**
	 *
	 * add datetime
	 *
	 * @param string $name
	 * @param string $label
	 * @param string $dateFormat
	 * @param string $timeFormat
	 * @return Controls\DateTimePicker
	 */
	public function addDateTime($name, $label = NULL, $dateFormat = 'j.n.Y', $timeFormat = 'H:i')
	{
		return $this[$name] = new Controls\DateTimePicker($dateFormat, $timeFormat, $label);
	}

	/**
	 * adds date range
	 *
	 * @param string $name
	 * @param string $label
	 * @param string $dateFormat
	 * @return Controls\DateTimePicker
	 */
	public function addDateRange($name, $label = NULL, $dateFormat = 'j.n.Y')
	{
		return $this[$name] = new Controls\DateRangePicker($dateFormat, $label);
	}

	/**
	 * add date
	 *
	 * @param string $name
	 * @param strign $label
	 * @param string $dateFormat
	 * @return Controls\DatePicker
	 */
	public function addDate($name, $label = NULL, $dateFormat = 'j.n.Y')
	{
		return $this[$name] = new Controls\DatePicker($dateFormat, $label);
	}

	/**
	 * add date time picker
	 *
	 * @param string $name
	 * @param strign $label
	 * @return \Nette\Forms\Controls\TextInput
	 */
	public function addTime($name, $label = NULL)
	{
		$control = $this->addText($name, $label);
		$control->setType('time');

		return $control;
	}

	/**
	 * adds multiple file upload
	 *
	 * @param string $name
	 * @param string $label
	 * @param int $maxFiles
	 * @return Controls\MultiUpload
	 */
	public function addMultiUpload($name, $label = NULL, $maxFiles = 999)
	{
		return $this[$name] = new Controls\MultiUpload($label, $maxFiles);
	}

	/**
	 * Adds a number input control to the form.
	 *
	 * @param string	control name
	 * @param string	label
	 * @param int   incremental number
	 * @param int   minimal value
	 * @param int   maximal value
	 * @return \Nette\Forms\Controls\TextInput
	 */
	public function addNumber($name, $label = NULL, $step = 1, $min = NULL, $max = NULL)
	{
		$item = $this->addText($name, $label);
		$item->setAttribute('step', $step)->setAttribute('type', 'number')
			->addCondition(self::FILLED)->addRule(self::NUMERIC);
		$range = [NULL, NULL];
		if ($min !== NULL) {
			$item->setAttribute('min', $min);
			$range[0] = $min;
		}
		if ($max !== NULL) {
			$item->setAttribute('max', $max);
			$range[1] = $max;
		}
		if ($range != [NULL, NULL]) {
			$item->addCondition(self::FILLED)->addRule(self::RANGE, NULL, $range);
		}

		return $item;
	}

	/**
	 * Adds a floating point number input control to the form.
	 *
	 * @param string	control name
	 * @param string	label
	 * @param int   minimal value
	 * @param int   maximal value
	 * @return \Nette\Forms\Controls\TextInput
	 */
	public function addFloat($name, $label = NULL, $min = NULL, $max = NULL)
	{
		$item = $this->addText($name, $label);
		$item->addCondition(self::FILLED)->addRule(self::FLOAT);
		$range = [NULL, NULL];
		if ($min !== NULL) {
			$item->setAttribute('min', $min);
			$range[0] = $min;
		}
		if ($max !== NULL) {
			$item->setAttribute('max', $max);
			$range[1] = $max;
		}
		if ($range != [NULL, NULL]) {
			$item->addCondition(self::FILLED)->addRule(self::RANGE, NULL, $range);
		}

		return $item;
	}

	/**
	 * @param string $name
	 * @param string $label
	 * @param bool   $prefix
	 * @param int	$maxLength
	 * @return Controls\PhoneInput
	 */
	public function addPhone($name, $label = NULL, $prefix = FALSE, $maxLength = NULL)
	{
		return $this[$name] = new Controls\PhoneInput($label, $maxLength, $prefix);
	}

	/**
	 * @param $name
	 * @param null $label
	 * @param null $maxLength
	 * @return Controls\UrlInput
	 */
	public function addUrl($name, $label = NULL, $maxLength = NULL)
	{
		return $this[$name] = new Controls\UrlInput($label, $maxLength);
	}

	/**
	 * Adds a boolean picker
	 *
	 * @param string	control name
	 * @param string	label
	 * @return Controls\BooleanInput
	 */
	public function addBoolean($name, $label = NULL)
	{
		return $this[$name] = new Controls\BooleanInput($label);
	}

	/**
	 * Adds a range input control to the form.
	 *
	 * @param string	control name
	 * @param string	label
	 * @param int   incremental number
	 * @param int   minimal value
	 * @param int   maximal value
	 * @return \Nette\Forms\Controls\TextInput
	 */
	public function addRange($name, $label = NULL, $step = 1, $min = NULL, $max = NULL)
	{
		$item = $this->addNumber($name, $label, $step, $min, $max);
		return $item->setAttribute('type', 'range');
	}

	/**
	 * Adds hidden form control used to store a non-displayed value.
	 *
	 * @param  string  control name
	 * @param  mixed   default value
	 * @return HiddenField
	 */
	public function addHidden($name, $default = NULL)
	{
		$control = new Controls\HiddenField;
		$control->setDefaultValue($default);
		return $this[$name] = $control;
	}

	/**
	 * @param  bool  return values as an array?
	 * @return Nette\ArrayHash|array
	 */
	public function getValues($asArray = FALSE)
	{
		$values = parent::getValues($asArray);

		foreach ($values as $key => &$value) {
			if ($value
				&& isset($this[$key]->control)
				&& isset($this[$key]->control->attrs['type'])
				&& $this[$key]->control->attrs['type'] === 'datetime'
			) {
				$value = new \Nette\DateTime($value);
			}
		}

		return $values;
	}

	/**
	 * resets all values
	 * @return self
	 */
	public function resetValues()
	{
		$values = array_keys(parent::getValues(TRUE));

		foreach ($values as $name) {
			if ($this[$name] instanceof Container) {
				$this[$name]->resetValues();
			} else {
				$this[$name]->value = NULL;
			}
		}

		return $this;
	}

	/**
	 * Fucking exception in fucking ChoiceControl::setValue() made me write this awful shitty fucking method.
	 *
	 * @param $values
	 * @author fprochazka
	 */
	public function forceSetValues($values)
	{
		$refl = new \ReflectionProperty(\Nette\Forms\Form::class, 'httpData');
		$refl->setAccessible(TRUE);
		$refl->setValue($this, (array) $values);

		/** @var BaseControl $control */
		foreach ($this->getControls() as $control) {
			if (!$control->isDisabled()) {
				$control->loadHttpData();
			}
		}
	}

	/**
	 * @param bool $val
	 */
	public static function setRenderColonSuffix($val = TRUE)
	{
		self::$renderColonSuffix = (bool) $val;
	}

	/**
	 * @return bool
	 */
	public static function getRenderColonSuffix()
	{
		return self::$renderColonSuffix;
	}

	/**
	 * @return boolean
	 */
	public static function getUseCsrfToken()
	{
		return self::$useCsrfToken;
	}

	/**
	 * @param boolean $useCsrfToken
	 */
	public static function setUseCsrfToken($useCsrfToken = TRUE)
	{
		self::$useCsrfToken = $useCsrfToken;
	}

	/**
	 * @return boolean
	 */
	public static function isSpamProtection()
	{
		return self::$spamProtection;
	}

	/**
	 * @param boolean $spamProtection
	 */
	public static function setSpamProtection($spamProtection = TRUE)
	{
		self::$spamProtection = $spamProtection;
	}

}
