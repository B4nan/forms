<?php

namespace Bargency\Tests\Forms;

use Tester\TestCase,
	Tester\Assert,
	Bargency\Forms\Form,
	Bargency\Forms\Container;

require __DIR__ . '/../bootstrap.php';

/**
 * form test
 *
 * @author Martin Adámek <adamek@bargency.com>
 */
class ContainerTest extends TestCase
{

	public function setUp()
	{
		// no setup
	}

	public function testFormControlsAdding()
	{
		$form = new Form;

		$container = $form->addContainer('container');
		Assert::true($container instanceof \Bargency\Forms\Container);
		$innerContainer = $container->addContainer('container');
		Assert::true($innerContainer instanceof \Bargency\Forms\Container);
		$tag = $container->addTag('tag');
		Assert::true($tag instanceof \Bargency\Forms\Controls\TagInput);
		$redactor = $container->addRedactor('redactor');
		Assert::true($redactor instanceof \Bargency\Forms\Controls\Redactor);
		$dateTime = $container->addDateTime('dateTime');
		Assert::true($dateTime instanceof \Bargency\Forms\Controls\DateTimePicker);
		$dateRange = $form->addDateRange('dateRange');
		Assert::true($dateRange instanceof \Bargency\Forms\Controls\DateRangePicker);
		$date = $container->addDate('date');
		Assert::true($date instanceof \Bargency\Forms\Controls\DatePicker);
		$time = $container->addTime('time');
		Assert::true($time instanceof \Nette\Forms\Controls\TextInput);
		$number = $container->addNumber('number', 'Number', 1, 1, 10);
		Assert::true($number instanceof \Nette\Forms\Controls\TextInput);
		$range = $container->addRange('range', 'Range', 1, 1, 10);
		Assert::true($range instanceof \Nette\Forms\Controls\TextInput);
//		$multiUpload = $form->addMultiUpload('multiUpload');
//		Assert::true($multiUpload instanceof \Bargency\Forms\Controls\MultiUpload);
		$hidden = $container->addHidden('hidden');
		Assert::true($hidden instanceof \Bargency\Forms\Controls\HiddenField);
	}

	public function testResetValues()
	{
		$container = new Container;
		$container->addText('a1')->value = 'abc1';
		$container->addText('b1')->value = 'abc2';
		$container->addText('c1')->value = 'abc3';

		$values = $container->getValues(TRUE);

		Assert::count(3, $values);
		Assert::same('abc1', $container['a1']->value);
		Assert::same('abc2', $container['b1']->value);
		Assert::same('abc3', $container['c1']->value);

		$container->resetValues();

		Assert::count(3, $values);
		Assert::same('', $container['a1']->value);
		Assert::same('', $container['b1']->value);
		Assert::same('', $container['c1']->value);
	}
	
}

// run test
(new ContainerTest)->run();
