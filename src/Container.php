<?php

namespace Bargency\Forms;

use Nette\Forms\Container as NContainer;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class Container extends NContainer
{

	/**
	 * Register
	 */
	public static function register()
	{
		NContainer::extensionMethod('addDate', function (NContainer $_this, $name, $label = NULL, $dateFormat = 'j.n.Y') {
			return $_this[$name] = new Controls\DatePicker($dateFormat, $label);
		});
	}

	/**
	 * Adds naming container to the form.
	 * @param  string  name
	 * @return Container
	 */
	public function addContainer($name)
	{
		$class = get_called_class();
		$control = new $class;
		$control->currentGroup = $this->currentGroup;
		return $this[$name] = $control;
	}

	/**
	 * @param  string  control name
	 * @param  string  caption
	 * @param  string  secret used for spam protection
	 * @return \Nette\Forms\Controls\SubmitButton
	 */
	public function addSubmit($name, $caption = NULL, $secret = 'nospam')
	{
		if (Form::isSpamProtection()) {
			$label = $this->translator->translate('Fill in "%s"', $secret);
			$noSpam = $this->addText('nospam', $label)
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
	 * @param string $name
	 * @param string $label
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
	 * @return Controls\Redactor
	 */
	public function addRedactor($name, $label = NULL)
	{
		return $this[$name] = new Controls\Redactor($label);
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
	 * Add Multiple file upload
	 *
	 * @param string $name
	 * @param string $label
	 * @param int $maxFiles
	 * @return Controls\MultiUpload
	 */
	public function addMultipleUpload($name, $label = NULL, $maxFiles = 999)
	{
		return $this[$name] = new Controls\MultiUpload($label, $maxFiles);
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
			->addCondition(Form::FILLED)->addRule(Form::NUMERIC);
		$range = array(NULL, NULL);
		if ($min !== NULL) {
			$item->setAttribute('min', $min);
			$range[0] = $min;
		}
		if ($max !== NULL) {
			$item->setAttribute('max', $max);
			$range[1] = $max;
		}
		if ($range != array(NULL, NULL)) {
			$item->addCondition(Form::FILLED)->addRule(Form::RANGE, NULL, $range);
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
		$item->addCondition(Form::FILLED)->addRule(Form::FLOAT);
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
			$item->addCondition(Form::FILLED)->addRule(Form::RANGE, NULL, $range);
		}

		return $item;
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
	 * resets all values inside container
	 * @return self
	 */
	public function resetValues()
	{
		$values = array_keys(parent::getValues(TRUE));

		foreach ($values as $name) {
			$this[$name]->value = NULL;
		}

		return $this;
	}

}
