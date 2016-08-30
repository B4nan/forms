<?php

namespace B4nan\Tests\Forms\Controls;

use B4nan\Forms\Controls\TagInput;
use Nette\Forms\Form;
use Nette\Utils\Html;
use Tester\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class TagInputTest extends TestCase
{

	private $control;

	public function setUp()
	{
		$this->control = new TagInput;
	}

	public function testGetControl()
	{
		$form = new Form;
		$form['redactor'] = $this->control;
		$control = $this->control->getControl();
		Assert::type(Html::class, $control);

		$html = (string) $control;
		Assert::type('string', $html);
	}

}

// run test
(new TagInputTest)->run();
