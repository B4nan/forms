<?php

namespace Bargency\Tests\Forms\Controls;

use Bargency\Forms\Controls\MultiUpload;
use Bargency\Forms\Controls\MultiUpload\Validators;
use Bargency\Forms\DI\FormsExtension;
use Bargency\Forms\Form;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Forms\Controls\UploadControl;
use Nette\Forms\IControl;
use Nette\Http\FileUpload;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../../bootstrap.container.php';

/**
 * form uploader validator test
 *
 * @author Martin Adámek <adamek@bargency.com>
 */
class ValidatorsTest extends TestCase
{

	/** @var Container */
	private $container;

	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function setUp()
	{
		$e = new FormsExtension;
		Form::setConfig($e->defaults);
		MultiUpload::register($this->container);
	}

	public function testValidateFilled()
	{
		$u = \Mockery::mock(IControl::class);
		$u->value = [];
		Assert::false(Validators::validateFilled($u));
		$u->value = [ 1 ]; // actual value does not matter
		Assert::true(Validators::validateFilled($u));
	}

	public function testValidateFileSize()
	{
		$u = \Mockery::mock(UploadControl::class);
		$file1 = \Mockery::mock(FileUpload::class)->shouldReceive('getSize')->once()->andReturn(100)->getMock();
		$file2 = \Mockery::mock(FileUpload::class)->shouldReceive('getSize')->once()->andReturn(1e5)->getMock();
		$values = [ $file1, $file2 ];
		$u->shouldReceive('getValue')->once()->andReturn($values);
		Assert::false(Validators::validateFileSize($u, 100));
		Assert::false(Validators::validateFileSize($u, 100 + 1e5 - 1));
		Assert::true(Validators::validateFileSize($u, 100 + 1e5));
	}

	public function testValidateMimeType()
	{
		$u = \Mockery::mock(UploadControl::class);
		$file1 = \Mockery::mock(FileUpload::class)->shouldReceive('getContentType')->once()->andReturn('image/png')->getMock();
		$file2 = \Mockery::mock(FileUpload::class)->shouldReceive('getContentType')->once()->andReturn('image/png')->getMock();
		$values = [ $file1, $file2 ];
		$u->shouldReceive('getValue')->once()->andReturn($values);
		Assert::true(Validators::validateMimeType($u, 'image/png'));
		Assert::false(Validators::validateMimeType($u, 'image/jpg'));
	}

}

// run test
run(new ValidatorsTest($container));
