<?php

namespace B4nan\Forms\Controls\MultiUpload;

use Nette\Http\FileUpload;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class Upload extends FileUpload
{

	/** @var string */
	private $filename;

	/** @var string */
	private $token;

	/** @var string */
	private $queueToken;

	/**
	 * @param string $queueToken
	 * @param string $token
	 * @param string $filename
	 * @param array $file
	 */
	public function __construct($queueToken, $token, $filename, array $file = NULL)
	{
		$this->queueToken = $queueToken;
		$this->token = $token;
		$this->filename = $filename;
		parent::__construct($file);
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

}
