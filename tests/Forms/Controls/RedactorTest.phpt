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
class RedactorTest extends TestCase
{

	private $control;

	public function setUp()
	{
		$this->control = new \B4nan\Forms\Controls\Redactor;
	}

	public function testGetControl()
	{
		$form = new \Nette\Forms\Form;
		$form['redactor'] = $this->control;
		$control = $this->control->getControl();
		Assert::type('\Nette\Utils\Html', $control);

		$html = (string) $control;
		$dom = DomQuery::fromHtml($html);
		Assert::true($dom->has('div.redactor_control'));
		Assert::true($dom->has('textarea.redactor'));
	}

}

// run test
(new RedactorTest)->run();
