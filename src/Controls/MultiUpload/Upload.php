<?php

namespace Bargency\Forms\Controls\MultiUpload;

use Nette\Object;
use Nette\Utils\FileSystem;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class Upload extends Object
{

	/** @var string */
	private $filename;

	/** @var string */
	private $name;

	/** @var string */
	private $token;

	/** @var string */
	private $queueToken;

	/**
	 * @param $queueToken
	 * @param string $token
	 * @param string $filename
	 * @param string $name
	 */
	public function __construct($queueToken, $token, $filename, $name = NULL)
	{
		$this->queueToken = $queueToken;
		$this->token = $token;
		$this->filename = $filename;
		$this->name = $name;
	}

	/**
	 * Get name provided by client.
	 * @return string
	 */
	public function getName()
	{
		return isset($this->name) ? $this->name : basename($this->filename);
	}

	/**
	 * @return string
	 */
	public function getQueueToken()
	{
		return $this->queueToken;
	}

	/**
	 * @return string
	 */
	public function getToken()
	{
		return $this->token;
	}

	/**
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * Move file to another location.
	 * @param string $location
	 * @return Upload
	 */
	public function move($location)
	{
		FileSystem::createDir(dirname($location));
		FileSystem::delete($location);
		if (!call_user_func(is_uploaded_file($this->filename) ? 'move_uploaded_file' : 'rename', $this->filename, $location)) {
			throw new \Nette\InvalidStateException("Unable to move uploaded file '$this->filename' to '$location'.");
		}
		chmod($location, 0666);
		$this->filename = $location;
		return $this;
	}

}
