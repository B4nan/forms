<?php

namespace Bargency\Forms\DI;

use Nette\PhpGenerator\ClassType,
	Nette\Configurator,
	Nette\DI\Compiler,
	Nette\DI\CompilerExtension;

/**
 * @author adamek
 */
class FormsExtension extends CompilerExtension
{

	/**
	 * Load configuration for Bargency Forms
	 * registers Form Macros
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$engine = $builder->getDefinition('nette.latteFactory');
		$install = 'Bargency\Forms\FormMacros::install';
		$engine->addSetup($install . '(?->getCompiler())', ['@self']);
	}

	/**
	 * Register MultiUpload control
	 *
	 * @param ClassType $class
	 */
	public function afterCompile(ClassType $class)
	{
		$initialize = $class->methods['initialize'];
//		$initialize->addBody('\Bargency\Forms\Controls\MultiUpload::register($this);'); // todo
		$initialize->addBody('\Bargency\Forms\Container::register();');
		$initialize->addBody('\Kdyby\Replicator\Container::register();');
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
