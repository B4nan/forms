<?php

namespace Bargency\Tests\Forms;

use Bargency\Forms\Controls\BooleanInput;
use Bargency\Forms\Controls\EditableSelectBox;
use Bargency\Forms\Controls\PhoneInput;
use Bargency\Forms\Controls\UrlInput;
use Bargency\Forms\DI\FormsExtension;
use Bargency\Forms\Form;
use Bargency\Forms\Container;
use Bargency\Forms\Controls\DatePicker;
use Bargency\Forms\Controls\DateRangePicker;
use Bargency\Forms\Controls\DateTimePicker;
use Bargency\Forms\Controls\HiddenField;
use Bargency\Forms\Controls\Redactor;
use Bargency\Forms\Controls\TagInput;
use Bargency\Forms\Controls\MultiUpload;
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
