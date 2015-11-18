<?php

date_default_timezone_set('Europe/Prague');

// Load Nette Framework
require __DIR__ . '/../vendor/autoload.php';

\Kdyby\TesterExtras\Bootstrap::setup(__DIR__);

function run(\Tester\TestCase $testCase) {
	$testCase->run(isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : NULL);
}
