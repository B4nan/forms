<?php

namespace Bargency\Forms\Controls;

use Nette\Forms\Form;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class UrlInput extends \Nette\Forms\Controls\TextInput
{

	/** @var string */
	const URL_REGEXP = '\b([\w]+:)?([\w\d-_\/]+\.)?([\w\d-_\/]+\.[a-z]{2,})\/?(\?[\w\d\%=]+)?';

	/** @var string */
	const URL_REGEXP_HTTP = '\b([\w]+:)([\w\d-_\/]+\.)?([\w\d-_\/]+\.[a-z]{2,})\/?(\?[\w\d\%=]+)?';

	/**
	 * @param string $label
	 * @param int $maxLength
	 */
	public function __construct($label = NULL, $maxLength = NULL)
	{
		parent::__construct($label, $maxLength);

		$this->addCondition(Form::FILLED)
			 ->addRule(Form::PATTERN, 'Given string is not valid URL', self::URL_REGEXP);
	}

	/**
	 * @param string $value
	 * @return UrlInput
	 */
	public function setValue($value)
	{
		if ($value && !preg_match('%' . self::URL_REGEXP_HTTP . '%s', (string) $value, $m)) {
			$value = 'http://' . $value;
		}
		parent::setValue($value);
		return $this;
	}

	/**
	 * @return string|NULL
	 */
	public function getValue()
	{
		$value = parent::getValue();

		if ($value) {
			return $value;
		}

		return NULL;
	}

}
