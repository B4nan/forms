<?php

namespace B4nan\Tests\Forms;

use B4nan\Forms\DI\FormsExtension;
use Nette\Configurator;
use Nette\DI\Container;
use Tester\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * form test
 *
 * @author Martin Adámek <adamek@bargency.com>
 */
class FormsExtensionTest extends TestCase
{

	public function testExtension()
	{
		$configurator = new Configurator;
		$configurator->setTempDirectory(TEMP_DIR);
		$configurator->addConfig(__DIR__ . '/../../config/config.neon', FALSE);

		$extension = new FormsExtension;
		$extension->register($configurator);

		$container = $configurator->createContainer();
		Assert::type(Container::class, $container);
	}

}

// run test
(new FormsExtensionTest)->run();
