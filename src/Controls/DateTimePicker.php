<?php

namespace Bargency\Forms\Controls;

use Nette\Forms\Form,
	Nette\Forms\Rules;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class DateTimePicker extends \Nette\Forms\Controls\BaseControl
{

	const VALID = ':dateTimeValid';

	const FORMAT_PATTERN = '%s %s';

	const NAME_DATE = 'date';

	const NAME_TIME = 'time';

	/** @var string */
	private $dateFormat;

	/** @var string */
	private $timeFormat;

	/** @var string */
	private $date;

	/** @var string */
	private $time;

	/** @var bool */
	private $requiredOnlyDate = FALSE;

	/**
	 * @param string
	 * @param string
	 * @param string|NULL
	 */
	public function __construct($dateFormat = 'Y-m-d', $timeFormat = 'H:i', $label = NULL)
	{
		parent::__construct($label);
		$this->dateFormat = $dateFormat;
		$this->timeFormat = $timeFormat;
	}

	/**
	 * @param \DateTimeInterface|NULL
	 * @return \DateInput
	 */
	public function setValue($value = NULL)
	{
		if ($value === NULL || $value === '') {
			$this->date = NULL;
			$this->time = NULL;
			return $this;
		} elseif (is_string($value) && strtotime($value)) {
			$value = new \DateTime($value);
		} elseif (!$value instanceof \DateTime) {
			$given = gettype($value);
			throw new \Nette\InvalidArgumentException("Value must be DateTimeInterface or NULL, '$given' given");
		}

		$this->date = $value->format($this->dateFormat);
		$this->time = $value->format($this->timeFormat);

		return $this;
	}

	public function setDate($value = NULL)
	{
		if ($value === NULL || $value === '') {
			$this->date = NULL;
		} elseif (is_string($value) && strtotime($value)) {
			$value = new \DateTime($value);
		} elseif (!$value instanceof \DateTime) {
			$given = gettype($value);
			throw new \Nette\InvalidArgumentException("Value must be instance of class DateTime or NULL, '$given' given");
		}

		$this->date = $value->format($this->dateFormat);
		return $this;
	}

	public function setTime($value = NULL)
	{
		if ($value === NULL || $value === '') {
			$this->time = NULL;
		} elseif (is_string($value) && strtotime($value)) {
			$value = new \DateTime($value);
		} elseif (!$value instanceof \DateTime) {
			$given = gettype($value);
			throw new \Nette\InvalidArgumentException("Value must be DateTimeInterface or NULL, '$given' given");
		}

		$this->time = $value->format($this->timeFormat);
		return $this;
	}

	/**
	 * @param bool|string $value 'date' when only date is required
	 * @return self
	 */
	public function setRequired($value = TRUE)
	{
		parent::setRequired((bool) $value);

		if ($value === 'date') { // only date is required
			$this->requiredOnlyDate = TRUE;
		}

		return $this;
	}

	/**
	 * @return \DateTime|NULL
	 */
	public function getValue()
	{
		if (!$this->date || (!$this->requiredOnlyDate && !$this->time)) {
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
		$date = new \DateTime($this->date);
		if ($this->time) {
			$t = explode(':', $this->time);
			$date->setTime($t[0], $t[1]);
		}
		return $date;
	}

	/**
	 * @return boolean
	 */
	public function isFilled()
	{
		return $this->date && ($this->requiredOnlyDate || $this->time);
	}

	public function loadHttpData()
	{
		$date = $this->getHttpData(Form::DATA_LINE, '[' . static::NAME_DATE . ']');
		$this->date = $date ?: NULL;
		$time = $this->getHttpData(Form::DATA_LINE, '[' . static::NAME_TIME . ']');
		$this->time = $time ?: NULL;
	}

	public function getControl()
	{
		$this->setOption('rendered', TRUE);

		$container = \Nette\Utils\Html::el();
		$container->add($this->getControlPart(static::NAME_DATE));
		$container->add($this->getControlPart(static::NAME_TIME));

		return $container;
	}

	public function getControlPart($key)
	{
		$name = $this->getHtmlName();

		if ($key === static::NAME_DATE) {
			$control = \Nette\Utils\Html::el('input')->name($name . '[' . static::NAME_DATE . ']');
			$control->data('date-format', $this->dateFormat);
			$control->value($this->date);
			$control->class('datepicker form-control');
			$control->type('text');

			if ($this->disabled) {
				$control->disabled($this->disabled);
			}

			return $control;
		} elseif ($key === static::NAME_TIME) {
			$control = \Nette\Utils\Html::el('input')->name($name . '[' . static::NAME_TIME . ']');
			$control->data('time-format', $this->timeFormat);
			$control->value($this->time);
			$control->class('timepicker form-control');
			$control->type('text');

			if ($this->disabled) {
				$control->disabled($this->disabled);
			}

			return $control;
		}

		throw new \Nette\InvalidArgumentException('Part ' . $key . ' does not exist');
	}

	public function getLabelPart()
	{
		return NULL;
	}


	public function validate()
	{
		parent::validate();
		if (!$this->isDisabled() && $this->isFilled() && $this->getWorkingValue() === FALSE) {
			$this->addError(Rules::$defaultMessages[static::VALID]);
		}
	}

}
