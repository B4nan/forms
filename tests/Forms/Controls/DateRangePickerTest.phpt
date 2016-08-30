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
class DateRangePickerTest extends TestCase
{

	private $control;

	public function setUp()
	{
		$this->control = new \B4nan\Forms\Controls\DateRangePicker;
	}

	public function getValues()
	{
		return array(
			array(NULL, NULL),
			array(
				array(
					'1.01.2014',
					'21.01.2014',
				),
				array(
					'from' => new \DateTime('1.01.2014 00:00:00'),
					'to' => new \DateTime('21.01.2014 23:59:59'),
				),
			),
			array(
				array(
					'1.01.2014 00:00:00',
					'21.01.2014 00:00:00',
				),
				array(
					'from' => new \DateTime('1.01.2014 00:00:00'),
					'to' => new \DateTime('21.01.2014 23:59:59'),
				),
			),
			array(
				array(
					new \DateTime('5.1.2014 00:00:00'),
					new \DateTime('21.01.2014 00:00:00'),
				),
				array(
					'from' => new \DateTime('5.1.2014 00:00:00'),
					'to' => new \DateTime('21.01.2014 23:59:59'),
				),
			),
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
		$this->control->value = array('2.1.2014', '5.2.2014');
		Assert::true($this->control->isFilled());
		$this->control->value = NULL;
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
		Assert::same('div', $control->getName());

		$html = (string) $control;
		$dom = DomQuery::fromHtml($html);
		Assert::true($dom->has('input[name="date"]'));

		$this->control->disabled = TRUE;
		$html = (string) $this->control->getControl();
		$dom = DomQuery::fromHtml($html);
		Assert::true($dom->has('input[name="date"][disabled]'));
	}

}

// run test
(new DateRangePickerTest)->run();
