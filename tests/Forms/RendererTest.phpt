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
use Bargency\Forms\Renderer;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\CsrfProtection;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;
use Tester\DomQuery;
use Tester\TestCase,
	Tester\Assert,
	Bargency\Forms\Form;

$container = require __DIR__ . '/../bootstrap.container.php';

/**
 * form renderer test
 *
 * @author Martin Adámek <adamek@bargency.com>
 */
class RendererTest extends TestCase
{

	/** @var \Nette\DI\Container */
	private $container;

	/**
	 * RendererTest constructor.
	 */
	public function __construct($container)
	{
		$this->container = $container;
	}

	public function setUp()
	{
		$e = new FormsExtension;
		Form::setConfig($e->defaults);
		MultiUpload::register($this->container);
	}

	public function testRender()
	{
		$form = new Form;
		$form->removeProtection();
		$form->addButton('btn');
		$form->addCancel();
		$form->addSelect('select', NULL, [1, 2, 3])->setRequired();
		$form->addCheckbox('cb', 'cb');
		$form->addCheckboxList('cbl', 'cbl', ['a', 'b']);
		$form->addMultiSelect('select2', NULL, [1, 2, 3]);
		$form->addTag('tag');
		$form->addRedactor('redactor');
		$form->addCKEditor('ckeditor');
		$form->addDateTime('dateTime');
		$form->addDateRange('dateRange');
		$form->addDate('date');
		$form->addTime('time');
		$form->addNumber('number', 'Number', 1, 1, 10);
		$form->addRange('range', 'Range', 1, 1, 10);
		$form->addUrl('url');
		$form->addBoolean('bool');
		$form->addEditableSelect('eselect', NULL, ['a' => 'A', 'b' => 'B']);
		$form->addSubmit('submit');
		$form->addPhone('phone', 'Tel')
			->setOption('input-append', 'append')
			->setOption('input-prepend', 'prepend');
		$form->addFloat('float', NULL, 5, 20);
		$form->addHidden('hidden');
		$form->addContainer('container');
		$form->addMultiUpload('multiUpload');
		$html = (string) $form;
		Assert::type('string', $html);

		// bs 2 renderer
		$form->setRenderer(new Renderer($form));
		$html = (string) $form;
		Assert::type('string', $html);
	}

	public function testButtons()
	{
		$form = new Form;
		$form->addCancel();
		$form->addSubmit('save');

		ob_start();
		$form->getRenderer()->renderButtons($form);
		$html = ob_get_contents();
		ob_end_clean();

		$dom = DomQuery::fromHtml($html);
		Assert::true($dom->has('input[type="submit"]')); // save
		Assert::true($dom->has('input[type="button"]')); // cancel
	}

}

// run test
(new RendererTest($container))->run();
