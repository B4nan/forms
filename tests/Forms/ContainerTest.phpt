<?php

namespace B4nan\Tests\Forms;

use B4nan\Forms\Controls\BooleanInput;
use B4nan\Forms\Controls\EditableSelectBox;
use B4nan\Forms\Controls\PhoneInput;
use B4nan\Forms\Controls\UrlInput;
use B4nan\Forms\DI\FormsExtension;
use B4nan\Forms\Form;
use B4nan\Forms\Container;
use B4nan\Forms\Controls\DatePicker;
use B4nan\Forms\Controls\DateRangePicker;
use B4nan\Forms\Controls\DateTimePicker;
use B4nan\Forms\Controls\HiddenField;
use B4nan\Forms\Controls\Redactor;
use B4nan\Forms\Controls\TagInput;
use B4nan\Forms\Controls\MultiUpload;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextInput;
use Tester\TestCase;
use Tester\Assert;

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
		$e = new FormsExtension;
		Form::setConfig($e->defaults);
	}

	public function testFormControlsAdding()
	{
		$form = new Form;

		$container = $form->addContainer('container');
		Assert::type(Container::class, $container);
		$innerContainer = $container->addContainer('container');
		Assert::type(Container::class, $innerContainer);
		$tag = $container->addTag('tag');
		Assert::type(TagInput::class, $tag);
		$redactor = $container->addRedactor('redactor');
		Assert::type(Redactor::class, $redactor);
		$dateTime = $container->addDateTime('dateTime');
		Assert::type(DateTimePicker::class, $dateTime);
		$dateRange = $container->addDateRange('dateRange');
		Assert::type(DateRangePicker::class, $dateRange);
		$date = $container->addDate('date');
		Assert::type(DatePicker::class, $date);
		$time = $container->addTime('time');
		Assert::type(TextInput::class, $time);
		$number = $container->addNumber('number', 'Number', 1, 1, 10);
		Assert::type(TextInput::class, $number);
		$range = $container->addRange('range', 'Range', 1, 1, 10);
		Assert::type(TextInput::class, $range);
		$hidden = $container->addHidden('hidden');
		Assert::type(HiddenField::class, $hidden);
		$url = $container->addUrl('url');
		Assert::type(UrlInput::class, $url);
		$boolean = $container->addBoolean('bool');
		Assert::type(BooleanInput::class, $boolean);
		$eselect = $container->addEditableSelect('eselect', NULL, ['a' => 'A', 'b' => 'B']);
		Assert::type(EditableSelectBox::class, $eselect);
		$submit = $container->addSubmit('submit');
		Assert::type(SubmitButton::class, $submit);
		$phone = $container->addPhone('phone');
		Assert::type(PhoneInput::class, $phone);
		$float = $container->addFloat('float', NULL, 5, 20);
		Assert::type(TextInput::class, $float);
//		$multiUpload = $form->addMultiUpload('multiUpload');
//		Assert::type(MultiUpload::class, $multiUpload);
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
run(new ContainerTest);
