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
class CKEditorTest extends TestCase
{

	private $control;

	public function setUp()
	{
		$this->control = new \Bargency\Forms\Controls\CKEditor;
	}

	public function testGetControl()
	{
		$form = new \Nette\Forms\Form;
		$form['redactor'] = $this->control;
		$control = $this->control->getControl();
		Assert::type('\Nette\Utils\Html', $control);

		$html = (string) $control;
		$dom = DomQuery::fromHtml($html);
		Assert::true($dom->has('div.ckeditor_control'));
		Assert::true($dom->has('textarea.ckeditor'));
	}

}

// run test
(new CKEditorTest)->run();
