<?php

namespace Bargency\Forms\Controls\MultiUpload;

use Nette\Object;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
final class UploadQueue extends Object
{

	/** @var string */
	private $id;

	/** @var Upload[] */
	private $uploads = [];

	public function __construct($id)
	{
		$this->id = $id;
	}

	/**
	 * Add uploaded file.
	 * @param Upload $upload
	 */
	public function addUpload(Upload $upload)
	{
		$this->uploads[] = $upload;
	}

	/**
	 * Get last uploaded file.
	 * @return Upload
	 */
	public function getLastUpload()
	{
		return end($this->uploads);
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get all uploaded files.
	 * @param array $tokens
	 * @return Upload[]
	 */
	public function getUploads(array $tokens)
	{
		return array_filter($this->uploads, function($item) use ($tokens) {
			return in_array($item->token, $tokens);
		});
	}

}
