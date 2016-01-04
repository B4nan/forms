<?php

namespace Bargency\Tests\Forms;

use Bargency\Forms\Container;
use Bargency\Forms\Controls\BooleanInput;
use Bargency\Forms\Controls\CKEditor;
use Bargency\Forms\Controls\DatePicker;
use Bargency\Forms\Controls\DateRangePicker;
use Bargency\Forms\Controls\DateTimePicker;
use Bargency\Forms\Controls\EditableSelectBox;
use Bargency\Forms\Controls\HiddenField;
use Bargency\Forms\Controls\MultiUpload;
use Bargency\Forms\Controls\PhoneInput;
use Bargency\Forms\Controls\Redactor;
use Bargency\Forms\Controls\TagInput;
use Bargency\Forms\Controls\UrlInput;
use Bargency\Forms\DI\FormsExtension;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\CsrfProtection;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;
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
		$e = new FormsExtension;
		Form::setConfig($e->defaults);
	}

	public function testFormControlsAdding()
	{
		$form = new Form;
		$form2 = new Form;

		$btn = $form->addButton('btn');
		Assert::type(Button::class, $btn);
		$cancel = $form->addCancel();
		Assert::type(Button::class, $cancel);
		$cancel2 = $form2->addCancel(function() {
			// callback
		});
		Assert::type(Button::class, $cancel2);
		$select = $form->addSelect('select', NULL, [1, 2, 3]);
		Assert::type(SelectBox::class, $select);
		$select2 = $form->addMultiSelect('select2', NULL, [1, 2, 3]);
		Assert::type(MultiSelectBox::class, $select2);
		$tag = $form->addTag('tag');
		Assert::type(TagInput::class, $tag);
		$redactor = $form->addRedactor('redactor');
		Assert::type(Redactor::class, $redactor);
		$ckeditor = $form->addCKEditor('ckeditor');
		Assert::type(CKEditor::class, $ckeditor);
		$dateTime = $form->addDateTime('dateTime');
		Assert::type(DateTimePicker::class, $dateTime);
		$dateRange = $form->addDateRange('dateRange');
		Assert::type(DateRangePicker::class, $dateRange);
		$date = $form->addDate('date');
		Assert::type(DatePicker::class, $date);
		$time = $form->addTime('time');
		Assert::type(TextInput::class, $time);
		$number = $form->addNumber('number', 'Number', 1, 1, 10);
		Assert::type(TextInput::class, $number);
		$range = $form->addRange('range', 'Range', 1, 1, 10);
		Assert::type(TextInput::class, $range);
		$url = $form->addUrl('url');
		Assert::type(UrlInput::class, $url);
		$boolean = $form->addBoolean('bool');
		Assert::type(BooleanInput::class, $boolean);
		$eselect = $form->addEditableSelect('eselect', NULL, ['a' => 'A', 'b' => 'B']);
		Assert::type(EditableSelectBox::class, $eselect);
		$submit = $form->addSubmit('submit');
		Assert::type(SubmitButton::class, $submit);
		$phone = $form->addPhone('phone');
		Assert::type(PhoneInput::class, $phone);
		$float = $form->addFloat('float', NULL, 5, 20);
		Assert::type(TextInput::class, $float);
		$form->addHidden('hidden');
		Assert::type(HiddenField::class, $form['hidden']);
		Assert::type(Html::class, $form['hidden']->getControl());
		$container = $form->addContainer('container');
		Assert::type(Container::class, $container);
		$multiUpload = $form->addMultiUpload('multiUpload');
		Assert::type(MultiUpload::class, $multiUpload);
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
		Assert::type(CsrfProtection::class, $form[Form::PROTECTOR_ID]);

		$form->removeProtection();
		Assert::false(isset($form[Form::PROTECTOR_ID]));
	}

}

// run test
(new FormTest)->run();
