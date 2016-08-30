<?php

namespace B4nan\Forms\Controls;

use Nette\Forms\Form;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class BooleanInput extends \Nette\Forms\Controls\RadioList
{

	/**
	 * @param string $label
	 */
	public function __construct($label = NULL)
	{
		parent::__construct($label, [
			TRUE => 'Yes',
			FALSE => 'No',
		]);
		$this->value = 0;
		$this->separatorPrototype->setName(NULL);
	}

	/**
	 * @param bool|int $value
	 * @return \B4nan\Forms\Controls\PhoneInput
	 */
	public function setValue($value)
	{
		if ($value !== NULL) {
			$value = (int) $value;
		}
		parent::setValue($value);
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getValue()
	{
		return (bool) parent::getValue();
	}

}
