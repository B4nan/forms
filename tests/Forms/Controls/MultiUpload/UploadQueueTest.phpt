<?php

namespace B4nan\Tests\Forms\Controls;

use B4nan\Forms\Controls\MultiUpload\Upload;
use B4nan\Forms\Controls\MultiUpload\UploadQueue;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * form uploader queue test
 *
 * @author Martin Adámek <adamek@bargency.com>
 */
class UploadQueueTest extends TestCase
{

	/** @var UploadQueue */
	private $queue;

	public function setUp()
	{
		$this->queue = new UploadQueue('id');
	}

	public function testIdGetter()
	{
		Assert::same('id', $this->queue->getId());
	}

	public function testUploads()
	{
		$u = new Upload('qtoken', 'token', 'filename');
		$this->queue->addUpload($u);
		$u2 = new Upload('qtoken2', 'token2', 'filename2');
		$this->queue->addUpload($u2);
		$u3 = $this->queue->getLastUpload();
		Assert::same($u2, $u3);
		Assert::equal([], $this->queue->getUploads([]));
		Assert::same([ $u ], $this->queue->getUploads([ 'token' ]));
	}

}

// run test
run(new UploadQueueTest);
