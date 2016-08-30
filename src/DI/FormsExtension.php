<?php

namespace B4nan\Forms\DI;

use B4nan\Forms\Container;
use B4nan\Forms\Controls\DateTimePicker;
use B4nan\Forms\Controls\MultiUpload\Uploader;
use B4nan\Forms\Controls\TagInput;
use B4nan\Forms\Form;
use B4nan\Forms\FormMacros3;
use B4nan\Forms\Renderer3;
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
	 * Load configuration for B4nan Forms
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
			$initialize->addBody('B4nan\Forms\Controls\MultiUpload::register($this);');
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
			$compiler->addExtension('B4nanForms', new FormsExtension);
		};
	}

}
