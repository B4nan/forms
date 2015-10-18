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
class PhoneInputTest extends TestCase
{

	private $control;

	public function setUp()
	{
		$this->control = new \Bargency\Forms\Controls\PhoneInput;
	}

	public function getValues()
	{
		return array(
			array(NULL, NULL),
			array('', NULL),
			array('123 123 123', 123123123),
			array('123123123', 123123123),
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

}

// run test
(new PhoneInputTest)->run();
