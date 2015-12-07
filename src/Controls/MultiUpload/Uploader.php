<?php

namespace Bargency\Forms\Controls\MultiUpload;

use Bargency\Forms\Controls\MultiUpload;
use Exception;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Http\FileUpload;
use Nette\Http\Request;
use Nette\Object;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class Uploader extends Object
{

	/** @var callable */
	public $onFileUploaded = [];

	/** @var callable */
	public $onUploadComplete = [];

	/** @var string */
	private $tempDir;

	/** @var Request */
	private $request;

	/** @var IStorage */
	private $cacheStorage;

	/**
	 * @param Request $request
	 * @param IStorage $storage
	 */
	public function __construct(Request $request, IStorage $storage)
	{
		$this->request = $request;
		$this->cacheStorage = $storage;
	}

	/**
	 * Handle upload.
	 * @param callable $onSuccess
	 * @throws Exception
	 */
	public function upload($onSuccess)
	{
		$token = $this->request->getQuery('token');
		if (! $token) {
			return;
		}
		$fileToken = $this->request->getQuery("file");

		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", FALSE);
		header("Pragma: no-cache");

		@set_time_limit(MultiUpload::MAX_LIFETIME);

		// Settings
		$targetDir = $this->tempDir;

		// Get a file name
		$fileName = $this->request->getPost('name');
		if (! $fileName && count($this->request->getFiles())) {
			$fileName = $this->request->getFile('file')->name;
		} else {
			$fileName = uniqid("file_");
		}

		$filePath = $targetDir . DIRECTORY_SEPARATOR . $token . $fileName;

		// Chunking might be enabled
		$chunk = (int) $this->request->getPost('chunk');
		$chunks = (int) $this->request->getPost('chunks');

		// Open temp file
		if (! $out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
			throw new Exception('Failed to open output stream.', 102);
		}

		/** @var FileUpload $file */
		if ($file = $this->request->getFile('file')) {
			if ($file->error || ! is_uploaded_file($file->temporaryFile)) {
				throw new Exception("Failed to move uploaded file. [error #$file->error]", 101);
			}

			// Read binary input stream and append it to temp file
			if (! $in = @fopen($file->temporaryFile, 'rb')) {
				throw new Exception('Failed to move uploaded file.', 101);
			}
		} else {
			if (! $in = @fopen("php://input", "rb")) {
				throw new Exception('Failed to move uploaded file.', 101);
			}
		}

		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}

		@fclose($out);
		@fclose($in);

		// Check if file has been uploaded
		if (! $chunks || $chunk === $chunks - 1) {
			rename("{$filePath}.part", $filePath);
			$this->cleanTempDir();
			$onSuccess(new Upload($token, $fileToken, $filePath, $fileName));
		}
	}

	/**
	 * Clean temp directory from old files, unfinished uploads.
	 */
	public function cleanTempDir()
	{
		$targetDir = $this->tempDir;

		if (! is_dir($targetDir)) {
			die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
		}

		foreach (Finder::findFiles('*')->in($this->tempDir) as $item) {
			// Remove temp file if it is older than the max age and is not the current file
			if ((filemtime($item) < time() - MultiUpload::MAX_LIFETIME)) {
				FileSystem::delete($item);
			}
		}
	}

	/**
	 * Handle incoming uploads
	 */
	public function handleUploads()
	{
		$this->upload(function(Upload $upload) {
			$uploadQueue = $this->restoreUploadQueue($upload->queueToken);
			$uploadQueue->addUpload($upload);
			$this->onFileUploaded($uploadQueue);
			$this->storeUploadQueue($uploadQueue);
		});
	}

	/**
	 * Fire callback when uploading is done.
	 * @param string $id
	 */
	public function handleUploadComplete($id)
	{
		$this->onUploadComplete($this->restoreUploadQueue($id));
	}

	/**
	 * Restore upload queue from previous request.
	 * @param $token
	 * @return UploadQueue
	 */
	public function restoreUploadQueue($token)
	{
		$cache = new Cache($this->cacheStorage, get_class());
		return $cache->load($token, function() use ($token) {
			return new UploadQueue($token);
		});
	}

	/**
	 * Store upload queue between requests.
	 * @param UploadQueue $uploadQueue
	 */
	public function storeUploadQueue(UploadQueue $uploadQueue)
	{
		$cache = new Cache($this->cacheStorage, get_class());
		$cache->save($uploadQueue->getId(), $uploadQueue, array(
			Cache::EXPIRE => '1 minutes',
			Cache::SLIDING => TRUE,
		));
	}

	/**
	 * @param string $tempPath
	 */
	public function setTempDir($tempPath)
	{
		$this->tempDir = $tempPath;
	}

	public function getBaseUrl()
	{
		return $this->request->url->baseUrl;
	}

}
