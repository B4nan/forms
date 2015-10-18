<?php

namespace Bargency\Forms\Controls;

use Nette\Forms\Form;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class PhoneInput extends \Nette\Forms\Controls\TextInput
{

	/** @var string */
	const FORMAT_NOPREFIX = '^([0-9]{3}) ?([0-9]{3}) ?([0-9]{3})$';

	/** @var string */
	const FORMAT_PREFIX = '^\+([0-9]{1,3}) ?([0-9]{3}) ?([0-9]{3}) ?([0-9]{3})$';

	/** @var string */
	const FORMAT_PREFIX_ONLY = '^\+[0-9]{1,3}$';

	/** @var bool use prefix plugin? */
	private $prefix;

	/**
	 * @param string $label
	 * @param int $maxLength
	 * @param bool $prefix use prefix plugin?
	 */
	public function __construct($label = NULL, $maxLength = NULL, $prefix = FALSE)
	{
		parent::__construct($label, $maxLength);

		$this->prefix = $prefix;

		if ($prefix) {
			$this->addCondition(Form::MIN_LENGTH, 5)
			     ->addRule(Form::MAX_LENGTH, 'Maximum length of phone number is %d chars', 16)
			     ->addRule(Form::PATTERN, 'Phone number should be in format +XXX XXX XXX XXX. Spaces are optional.', self::FORMAT_PREFIX);
		} else {
			$this->addCondition(Form::FILLED)
			     ->addRule(Form::MAX_LENGTH, 'Maximum length of phone number is %d chars', 11)
			     ->addRule(Form::PATTERN, 'Phone number should be in format XXX XXX XXX. Spaces are optional.', self::FORMAT_NOPREFIX);
		}
	}

	/**
	 * Generates control's HTML element.
	 * @return \Nette\Utils\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();

		if ($this->prefix) {
			$control->addClass('pp-phone-prefix-input');
		}

		return $control;
	}

	/**
	 * @param string $value
	 * @return \Bargency\Forms\Controls\PhoneInput
	 */
	public function setValue($value)
	{
		if (preg_match('/' . self::FORMAT_NOPREFIX . '/', (string) $value, $m)) {
			$value = "$m[1] $m[2] $m[3]";
		}
		if (preg_match('/' . self::FORMAT_PREFIX . '/', (string) $value, $m)) {
			$value = "+$m[1] $m[2] $m[3] $m[4]";
		}
		parent::setValue($value);
		return $this;
	}

	/**
	 * @return int
	 */
	public function getValue()
	{
		$value = str_replace(' ', '', parent::getValue());

		if (preg_match('/' . self::FORMAT_PREFIX_ONLY . '/', (string) $value, $m)) {
			$value = '';
		}

		if ($value && !$this->prefix) {
			return (int) $value;
		} elseif ($value) {
			return $value;
		}

		return NULL;
	}

}
