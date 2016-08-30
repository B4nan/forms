<?php

namespace B4nan\Tests\Forms\Controls;

use Tester\TestCase,
	Tester\Assert,
	B4nan\Forms\Container;

require __DIR__ . '/../../bootstrap.php';

/**
 * form test
 *
 * @author Martin Adámek <adamek@bargency.com>
 */
class DatePickerTest extends TestCase
{

	private $control;

	public function setUp()
	{
		$this->control = new \B4nan\Forms\Controls\DatePicker;
	}

	public function getValues()
	{
		return array(
			array(NULL, NULL),
			array('', NULL),
			array('+1 day', (new \DateTime('+1 day'))->setTime(0, 0, 0)),
			array('2.1.2014', new \DateTime('2.1.2014 00:00:00')),
			array('21.01.2014', new \DateTime('21.01.2014 00:00:00')),
			array(new \DateTime('21.01.2014 00:00:00'), new \DateTime('21.01.2014 00:00:00')),
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
		$form['date'] = $this->control;
		$control = $this->control->getControl();
		Assert::type('\Nette\Utils\Html', $control);
		Assert::same('input', $control->getName());
		Assert::same('text', $control->type);
		Assert::same('date[date]', $control->name);

		$this->control->disabled = TRUE;
		$control = $this->control->getControl();
		Assert::true($control->disabled);
	}

}

// run test
(new DatePickerTest)->run();
