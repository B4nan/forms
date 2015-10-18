<?php

namespace Bargency\Tests\Forms\Controls;

use Tester\TestCase,
	Tester\Assert,
	Tester\DomQuery;

require __DIR__ . '/../../bootstrap.php';

/**
 * @author Martin Adámek <adamek@bargency.com>
 * @skip
 */
class TagInputTest extends TestCase
{

	private $control;

	public function setUp()
	{
		$this->control = new \Bargency\Forms\Controls\TagInput;
	}

	public function testGetControl()
	{
		$form = new \Nette\Forms\Form;
		$form['redactor'] = $this->control;
		$control = $this->control->getControl();
		Assert::type('\Nette\Utils\Html', $control);

		echo $html = (string) $control;exit;

		$dom = DomQuery::fromHtml($html);
		Assert::true($dom->has('div.redactor_control'));
		Assert::true($dom->has('textarea.redactor'));
	}

}

// run test
(new TagInputTest)->run();
