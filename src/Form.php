<?php

namespace Bargency\Forms;

use Nette\Application\UI\Form as NForm;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IContainer;
use Nette\Forms\Validator;
use Nette\InvalidStateException;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class Form extends NForm
{

	use FormControlTrait;

	/** @var int insert form */
	const TYPE_INSERT = 0;

	/** @var int update form */
	const TYPE_UPDATE = 1;

	/** @var int other form */
	const TYPE_OTHER = 2;

	/** @var array */
	private static $config;

	/**
	 * @param IContainer $parent
	 * @param string $name
	 */
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$renderer = self::getOption('renderer');
		$this->setRenderer(new $renderer($this));

		if (self::getOption('csrfToken')) {
			$this->addProtection('The form has expired. Please re-submit.'); // CSRF protection
		}

		$this->monitor(Presenter::class);

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

		if (isset($presenter->translator)) {
			$this->setTranslator($presenter->translator);
		}

		$macros = self::getOption('macros');
		$macros::setFormClasses($this);
	}

	/**
	 * @param array $config
	 */
	public static function setConfig(array $config)
	{
		self::$config = $config;
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public static function setOption($name, $value)
	{
		self::$config[$name] = $value;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public static function getOption($name)
	{
		if (! isset(self::$config[$name])) {
			throw new InvalidStateException("Option $name does not exist!");
		}
		return self::$config[$name];
	}

}
