<?php

namespace Bargency\Forms;

use Bargency\Forms\Controls\HiddenField;
use Bargency\Forms\Controls\TagInput;
use Kdyby\Replicator\Container as ReplicatorContainer;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

/**
 * @method ReplicatorContainer addDynamic(string $name, callable $factory, int $createDefault = 0, bool $forceDefault = FALSE)
 * @author Martin Adámek <adamek@bargency.com>
 */
trait FormControlTrait
{

	/**
	 * {@inheritdoc}
	 */
	public function addSelect($name, $label = NULL, array $items = NULL, $size = NULL)
	{
		$control = parent::addSelect($name, $label, $items, $size);
		$control->checkAllowedValues = FALSE;
		return $control;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addMultiSelect($name, $label = NULL, array $items = NULL, $size = NULL)
	{
		$control = parent::addMultiSelect($name, $label, $items, $size);
		$control->checkAllowedValues = FALSE;
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
	 * @param  string  control name
	 * @param  string  caption
	 * @return SubmitButton
	 */
	public function addSubmit($name, $caption = NULL)
	{
		if (Form::getOption('spamProtection') && ! isset($this['website_'])) {
			$noSpam = $this->addText('website_', 'Website')
							->addRule(Form::BLANK, 'You are a spambot!')
							->setOmitted();
			$noSpam->getControlPrototype()->class('hidden');
			$noSpam->getLabelPrototype()->class('hidden');
		}

		$control = parent::addSubmit($name, $caption);
		$control->controlPrototype->class[] = 'btn btn-medium';
		return $control;
	}

	/**
	 * @param string $name
	 * @param string $caption
	 * @return SubmitButton
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
		$control->setOmitted();
		$control->controlPrototype->class[] = 'btn btn-medium';

		return $control;
	}

	/**
	 * Adds named container to the form.
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
	 * @param string $name
	 * @param string $label
	 * @param bool $returnString
	 * @return TagInput provides fluent interface
	 */
	public function addTag($name, $label = NULL, $returnString = FALSE)
	{
		$this[$name] = new Controls\TagInput($label, $returnString);
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
	 * add CKEditor js
	 *
	 * @param string $name
	 * @param string $label
	 * @return Controls\CKEditor
	 */
	public function addCKEditor($name, $label = NULL)
	{
		return $this[$name] = new Controls\CKEditor($label);
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
	 * @param string $label
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
	 * @param string $label
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
	 * @param string $name
	 * @param string $label
	 * @param int $step incremental number
	 * @param int $min minimal value
	 * @param int $max maximal value
	 * @return \Nette\Forms\Controls\TextInput
	 */
	public function addNumber($name, $label = NULL, $step = 1, $min = NULL, $max = NULL)
	{
		$item = $this->addText($name, $label);
		$item->setAttribute('step', $step)->setAttribute('type', 'number')
				->addCondition(Form::FILLED)->addRule(Form::NUMERIC);
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
	 * Adds a floating point number input control to the form.
	 *
	 * @param string $name
	 * @param string $label
	 * @param int $min minimal value
	 * @param int $max maximal value
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
	 * @param string $name
	 * @param string $label
	 * @param bool $default
	 * @return Controls\BooleanInput
	 */
	public function addBoolean($name, $label = NULL, $default = NULL)
	{
		$c = new Controls\BooleanInput($label);
		$c->value = $default;
		return $this[$name] = $c;
	}

	/**
	 * Adds a range input control to the form.
	 *
	 * @param string $name
	 * @param string $label
	 * @param int $step incremental number
	 * @param int $min minimal value
	 * @param int $max maximal value
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
	 * @param  string  $name name
	 * @param  mixed   $default value
	 * @return HiddenField
	 */
	public function addHidden($name, $default = NULL)
	{
		$control = new Controls\HiddenField;
		$control->setDefaultValue($default);
		return $this[$name] = $control;
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

}
