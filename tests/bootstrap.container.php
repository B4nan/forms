<?php

require __DIR__ . '/bootstrap.php';

// create DI container
$configurator = new Nette\Configurator;
$configurator->setDebugMode(FALSE);
$configurator->setTempDirectory(TEMP_DIR);
$configurator->addParameters(array(
	'tempDir' => TEMP_DIR,
));

$configurator->addConfig(__DIR__ . '/config/config.neon');
if (file_exists(__DIR__ . '/config/config.local.neon')) {
	$configurator->addConfig(__DIR__ . '/config/config.local.neon');
}

\Nette\Utils\FileSystem::createDir(TEMP_DIR . '/sessions');

$container = $configurator->createContainer();
$container->router[] = new \Nette\Application\Routers\Route('<presenter>/<action>[/<id>]', 'Dashboard:default');

return $container;
