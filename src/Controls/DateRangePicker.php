<?php

namespace B4nan\Forms\Controls;

use Nette\Forms\Form,
	Nette\Forms\Rules;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class DateRangePicker extends \Nette\Forms\Controls\BaseControl
{

	/** @var string */
	const VALID = ':dateValid';

	/** @var string */
	private $dateFormat;

	/** @var array|NULL */
	private $range;

	/**
	 * @param string
	 * @param string
	 * @param string|NULL
	 */
	public function __construct($dateFormat = 'j.n.Y', $label = NULL)
	{
		parent::__construct($label);
		$this->dateFormat = $dateFormat;
	}

	/**
	 * @param array|NULL
	 * @return self
	 */
	public function setValue($value = NULL)
	{
		if ($value === NULL) {
			$this->range = NULL;
			return $this;
		} elseif (is_array($value) && count($value) === 2) {
			$from = $value[0] instanceof \DateTime ? $value[0] : new \DateTime($value[0]);
			$to   = $value[1] instanceof \DateTime ? $value[1] : new \DateTime($value[1]);
			$this->range = array(
				$from->format($this->dateFormat),
				$to->format($this->dateFormat),
			);
		} else {
			$given = gettype($value);
			throw new \Nette\InvalidArgumentException("Value must be [from, to] array or NULL, '$given' given");
		}

		return $this;
	}

	/**
	 * @return array|NULL
	 */
	public function getValue()
	{
		if (empty($this->range)) {
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
		$from = \DateTime::createFromFormat($this->dateFormat, $this->range[0])->setTime(0, 0, 0);
		$to = \DateTime::createFromFormat($this->dateFormat, $this->range[1])->setTime(23, 59, 59);

		if ($from && $to) {
			return array(
				'from' => $from,
				'to' => $to,
			);
		}

		return FALSE;
	}

	/**
	 * @return boolean
	 */
	public function isFilled()
	{
		return !empty($this->range);
	}

	public function loadHttpData()
	{
		$range = $this->getHttpData(Form::DATA_LINE);
		$range = str_replace(' - ', '-', $range);
		$this->range = explode('-', $range);
	}

	public function getControl()
	{
		$this->setOption('rendered', TRUE);
		$name = $this->getHtmlName();

		$input = \Nette\Utils\Html::el('input')->name($name)
											   ->type('text')
											   ->data('date-format', $this->dateFormat)
											   ->value(implode(' - ', (array) $this->range));
		$calendar = \Nette\Utils\Html::el('div')->class('date-range-calendar');
		$container = \Nette\Utils\Html::el('div')->class('date-range')
												 ->add($input)
												 ->add($calendar);

		if ($this->disabled) {
			$input->disabled($this->disabled);
		}

		return $container;
	}

	public function validate()
	{
		parent::validate();
		if (!$this->isDisabled() && $this->isFilled() && $this->getWorkingValue() === FALSE) {
			$this->addError(Rules::$defaultMessages[static::VALID]);
		}
	}

}
