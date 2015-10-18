<?php

namespace Bargency\Tests\Forms\Controls;

use Tester\TestCase,
	Tester\Assert,
	Tester\DomQuery;

require __DIR__ . '/../../bootstrap.php';

/**
 * form test
 *
 * @author Martin Adámek <adamek@bargency.com>
 */
class DateTimePickerTest extends TestCase
{

	private $control;

	public function setUp()
	{
		$this->control = new \Bargency\Forms\Controls\DateTimePicker;
	}

	public function getValues()
	{
		return array(
			array(NULL, NULL),
			array('', NULL),
			array('2.1.2014', new \DateTime('2.1.2014')),
			array('2.1.2014 22:15:00', new \DateTime('2.1.2014 22:15:00')),
			array('21.01.2014', new \DateTime('21.01.2014')),
			array(new \DateTime('21.01.2014 00:10:00'), new \DateTime('21.01.2014 00:10:00')),
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

		$html = (string) $control;
		$dom = DomQuery::fromHtml($html);
		// workaround for input[name="date[form]"]
		Assert::true((bool) $dom->xpath('//input[@name=\'date[date]\']'));
		Assert::true((bool) $dom->xpath('//input[@name=\'date[time]\']'));
		Assert::true($dom->has('input[type="text"].datepicker'));
		Assert::true($dom->has('input[type="text"].timepicker'));

		$this->control->disabled = TRUE;
		$html = (string) $this->control->getControl();
		$dom = DomQuery::fromHtml($html);
		Assert::true($dom->has('input[type="text"][disabled].datepicker'));
		Assert::true($dom->has('input[type="text"][disabled].timepicker'));
	}

}

// run test
(new DateTimePickerTest)->run();
