<?php

namespace Bargency\Tests\Forms;

use Tester\TestCase,
	Tester\Assert,
	Bargency\Forms\Form;

require __DIR__ . '/../bootstrap.php';

/**
 * form test
 *
 * @author Martin Adámek <adamek@bargency.com>
 */
class FormTest extends TestCase
{

	public function setUp()
	{
		// no setup
	}

	public function testFormControlsAdding()
	{
		$form = new Form;

		$tag = $form->addTag('tag');
		Assert::type('\Bargency\Forms\Controls\TagInput', $tag);
		$redactor = $form->addRedactor('redactor');
		Assert::true($redactor instanceof \Bargency\Forms\Controls\Redactor);
		$dateTime = $form->addDateTime('dateTime');
		Assert::true($dateTime instanceof \Bargency\Forms\Controls\DateTimePicker);
		$dateRange = $form->addDateRange('dateRange');
		Assert::true($dateRange instanceof \Bargency\Forms\Controls\DateRangePicker);
		$date = $form->addDate('date');
		Assert::true($date instanceof \Bargency\Forms\Controls\DatePicker);
		$time = $form->addTime('time');
		Assert::true($time instanceof \Nette\Forms\Controls\TextInput);
		$number = $form->addNumber('number', 'Number', 1, 1, 10);
		Assert::true($number instanceof \Nette\Forms\Controls\TextInput);
		$range = $form->addRange('range', 'Range', 1, 1, 10);
		Assert::true($range instanceof \Nette\Forms\Controls\TextInput);
//		$multiUpload = $form->addMultiUpload('multiUpload');
//		Assert::true($multiUpload instanceof \Bargency\Forms\Controls\MultiUpload);
		$form->addHidden('hidden');
		Assert::true($form['hidden'] instanceof \Bargency\Forms\Controls\HiddenField);
		Assert::true($form['hidden']->getControl() instanceof \Nette\Utils\Html);

		$container = $form->addContainer('container');
		Assert::true($container instanceof \Bargency\Forms\Container);
	}

	public function testResetValues()
	{
		$form = new Form;
		$form->addText('a1')->value = 'abc1';
		$form->addText('b1')->value = 'abc2';
		$form->addText('c1')->value = 'abc3';

		$values = $form->getValues(TRUE);

		Assert::count(3, $values);
		Assert::same('abc1', $form['a1']->value);
		Assert::same('abc2', $form['b1']->value);
		Assert::same('abc3', $form['c1']->value);

		$form->resetValues();

		Assert::count(3, $values);
		Assert::same('', $form['a1']->value);
		Assert::same('', $form['b1']->value);
		Assert::same('', $form['c1']->value);
	}

	public function testRemoveProtection()
	{
		$form = new Form;
		Assert::type('\Nette\Forms\Controls\CsrfProtection', $form[Form::PROTECTOR_ID]);

		$form->removeProtection();
		Assert::false(isset($form[Form::PROTECTOR_ID]));
	}

}

// run test
(new FormTest)->run();
