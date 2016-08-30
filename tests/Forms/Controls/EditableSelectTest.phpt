<?php

namespace B4nan\Tests\Forms\Controls;

use Tester\TestCase,
	Tester\Assert,
	Tester\DomQuery;

require __DIR__ . '/../../bootstrap.php';

/**
 * form test
 *
 * @author Martin Adámek <adamek@bargency.com>
 */
class EditableSelectTest extends TestCase
{

	private $control;

	private $items = array(
		'a' => 'A',
		'b' => 'B',
		'c' => 'C',
		'other' => 'Other',
	);

	public function setUp()
	{
		$this->control = new \B4nan\Forms\Controls\EditableSelectBox('label', $this->items, 'other');
	}

	public function getValues()
	{
		return array(
			array(NULL, NULL),
			array('', NULL),
			array('test', 'test'),
			array('c', 'c'),
		);
	}

	/**
	 * @dataProvider getValues
	 */
	public function testSetGetValue($value, $expected)
	{
		$this->control->setValue($value);
		Assert::equal($expected, $this->control->getValue());
	}

	public function testIsFilled()
	{
		Assert::false($this->control->isFilled());
		$this->control->value = '2.1.2014';
		Assert::true($this->control->isFilled());
		$this->control->value = '';
		Assert::false($this->control->isFilled());
	}

	public function testValidate()
	{
		$form = new \Nette\Forms\Form;
		$form['date'] = $this->control;
		$form->addSubmit('send', 'Send');

		$this->control->validate();
		$errors = $this->control->getErrors();
		Assert::count(0, $errors);

//		$this->control->loadHttpData();
//		$this->control->validate();
//		$errors = $this->control->getErrors();
//		Assert::count(1, $errors);
	}

	public function testGetControl()
	{
		$form = new \Nette\Forms\Form;
		$form['editable_select'] = $this->control;
		$control = $this->control->getControl();
		Assert::type('\Nette\Utils\Html', $control);

		$html = (string) $control;
		$dom = DomQuery::fromHtml($html);
		Assert::true($dom->has('select[name="editable_select__eselect"]'));
		Assert::true($dom->has('input[type="text"][name="editable_select"]'));

		$this->control->disabled = TRUE;
		$html = (string) $this->control->getControl();
		$dom = DomQuery::fromHtml($html);
		Assert::true($dom->has('select[name="editable_select__eselect"][disabled]'));
		Assert::true($dom->has('input[type="text"][name="editable_select"][disabled]'));
	}

	public function testSetGetItems()
	{
		$items = $this->control->getItems();
		Assert::equal($this->items, $items);

		$new = array(
			'Abc' => array(
				'a' => 'A',
				'b' => 'B',
			),
			'Def' => array(
				'd' => 'D',
				'e' => 'E',
			),
		);
		$this->control->setItems($new);
		$items = $this->control->getItems();
		Assert::equal($new, $items);

		$new = array(
			'Aaa',
			'Bbb',
			'Ccc',
		);
		$expected = array(
			'Aaa' => 'Aaa',
			'Bbb' => 'Bbb',
			'Ccc' => 'Ccc',
		);
		$this->control->setItems($new, FALSE);
		$items = $this->control->getItems();
		Assert::equal($expected, $items);
	}

	public function testGetLabel()
	{
		$form = new \Nette\Forms\Form;
		$form['eselect'] = $this->control;

		$label = $this->control->getLabel();
		Assert::type('\Nette\Utils\Html', $label);

		$html = (string) $label;
		$dom = DomQuery::fromHtml($html);
		Assert::true($dom->has('label'));
	}

	public function testSetGetPrompt()
	{
		$prompt = $this->control->getPrompt();
		Assert::false($prompt);

		$this->control->setPrompt('test');
		$prompt = $this->control->getPrompt();
		Assert::same($prompt, 'test');
	}

	public function testSetGetOther()
	{
		$other = $this->control->getOther();
		Assert::same($other, 'other');

		$this->control->setOther(FALSE);
		$other = $this->control->getOther();
		Assert::false($other);
	}

}

// run test
(new EditableSelectTest)->run();
