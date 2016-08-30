<?php

namespace B4nan\Tests\Forms\Controls;

use B4nan\Forms\Controls\MultiUpload\Upload;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * form uploader entity test
 *
 * @author Martin Adámek <adamek@bargency.com>
 */
class UploadTest extends TestCase
{

	/** @var Upload */
	private $upload;

	public function setUp()
	{
		$this->upload = new Upload('qt', 't', 'fn');
	}

	public function testUpload()
	{
		Assert::same('fn', $this->upload->getFilename());
		Assert::same('qt', $this->upload->getQueueToken());
		Assert::same('t', $this->upload->getToken());
	}

}

// run test
run(new UploadTest);
