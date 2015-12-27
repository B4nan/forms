<?php

namespace Bargency\Forms\DI;

use Bargency\Forms\Container;
use Bargency\Forms\Controls\DateTimePicker;
use Bargency\Forms\Controls\MultiUpload\Uploader;
use Bargency\Forms\Controls\TagInput;
use Bargency\Forms\Form;
use Bargency\Forms\FormMacros3;
use Bargency\Forms\Renderer3;
use Kdyby\Replicator;
use Nette\Forms\Validator;
use Nette\PhpGenerator\ClassType;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

/**
 * @author adamek
 */
class FormsExtension extends CompilerExtension
{

	/** @var array */
	public $defaults = [
		'renderer' => Renderer3::class,
		'macros' => FormMacros3::class,
		'multiupload' => FALSE,
		'spamProtection' => TRUE,
		'csrfToken' => TRUE,
		'renderColonSuffix' => TRUE,
	];

	/**
	 * Load configuration for Bargency Forms
	 * registers Form Macros
	 */
	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();
		$engine = $builder->getDefinition('nette.latteFactory');
		$install = $config['macros'] . '::install';
		$engine->addSetup($install . '(?->getCompiler())', ['@self']);
		$builder->addDefinition($this->prefix('forms'))
				->setClass(Uploader::class);
	}

	/**
	 * Register MultiUpload control
	 *
	 * @param ClassType $class
	 */
	public function afterCompile(ClassType $class)
	{
		$initialize = $class->methods['initialize'];
		$config = $this->getConfig($this->defaults);
		if ($config['multiupload']) {
			$initialize->addBody('Bargency\Forms\Controls\MultiUpload::register($this);');
		}
		$initialize->addBody(Container::class . '::register();');
		$initialize->addBody(Replicator\Container::class . '::register();');
		$initialize->addBody(Form::class . '::setConfig(?);', [$config]);

		Validator::$messages[TagInput::UNIQUE] = 'Please insert each tag only once.';
		Validator::$messages[TagInput::ORIGINAL] = 'Please do use only suggested tags.';
		Validator::$messages[DateTimePicker::VALID] = 'Invalid date time';
	}

	/**
	 * @param Configurator $config
	 */
	public static function register(Configurator $config)
	{
		$config->onCompile[] = function (Configurator $config, Compiler $compiler) {
			$compiler->addExtension('BargencyForms', new FormsExtension);
		};
	}

}
