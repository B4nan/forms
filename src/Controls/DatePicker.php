<?php

namespace B4nan\Forms\Controls;

use Nette\Forms\Form,
	Nette\Forms\Rules;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class DatePicker extends \Nette\Forms\Controls\BaseControl
{

	const VALID = ':dateValid';

	const FORMAT_PATTERN = '%s %s';

	const NAME_DATE = 'date';

	/** @var string */
	private $dateFormat;

	/** @var string */
	private $date;

	/**
	 * @param string
	 * @param string
	 * @param string|NULL
	 */
	public function __construct($dateFormat = 'Y-m-d', $label = NULL)
	{
		parent::__construct($label);
		$this->dateFormat = $dateFormat;
	}

	/**
	 * @param \DateTimeInterface|NULL
	 * @return \DateInput
	 */
	public function setValue($value = NULL)
	{
		if ($value === NULL || $value === '') {
			$this->date = NULL;
			return $this;
		} elseif (is_string($value) && strtotime($value)) {
			$value = new \DateTime($value);
		} elseif (!$value instanceof \DateTime) {
			throw new \Nette\InvalidArgumentException('Value must be DateTimeInterface or NULL');
		}

		$this->date = $value->format($this->dateFormat);

		return $this;
	}

	/**
	 * @return \DateTime|NULL
	 */
	public function getValue()
	{
		if (empty($this->date)) {
			return NULL;
		}

		$value = $this->getWorkingValue();

		if ($value === FALSE) {
			return NULL;
		}

		return $value;
	}

	public function getWorkingValue()
	{
		return \DateTime::createFromFormat($this->dateFormat, $this->date)->setTime(0, 0, 0);
	}

	/**
	 * @return boolean
	 */
	public function isFilled()
	{
		return !empty($this->date);
	}

	public function loadHttpData()
	{
		$this->date = $this->getHttpData(Form::DATA_LINE, '[' . static::NAME_DATE . ']');
	}

	public function getControl()
	{
		$this->setOption('rendered', TRUE);
		$name = $this->getHtmlName();

		$control = \Nette\Utils\Html::el('input')->name($name . '[' . static::NAME_DATE . ']');
		$control->data('date-format', $this->dateFormat);
		$control->value($this->date);
		$control->class('datepicker form-control');
		$control->type('text');

		if ($this->disabled) {
			$control->disabled($this->disabled);
		}

		return $control;
	}

	public function validate()
	{
		parent::validate();
		if (!$this->isDisabled() && $this->isFilled() && $this->getWorkingValue() === FALSE) {
			$this->addError(Rules::$defaultMessages[static::VALID]);
		}
	}

}
