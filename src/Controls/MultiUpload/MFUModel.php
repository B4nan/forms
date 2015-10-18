<?php

namespace Bargency\Forms\Controls\MultiUpload;

use Bargency\Entities\Admin\MfuFile,
	Bargency\Models\Model;

/**
 * MultiUpload model
 *
 * @author Martin Adámek <adamek@bargency.com>
 */
class MFUModel extends Model
{

	/** @var string */
	const ENTITY = MfuFile::class;

	/** @var string */
	private $tempPath;

	/** @var int */
	private $lifeTime;

	/**
	 * sets life time of temporary file
	 *
	 * @param int $time
	 * @return $this
	 */
	public function setLifeTime($time)
	{
		$this->lifeTime = $time;
		return $this;
	}

	/**
	 * Gets queue (if needed create)
	 *
	 * @param string $id
	 * @return MFUQueue
	 */
	public function getQueue($id)
	{
		return $this->createQueueObj($id);
	}

	/**
	 * Factory for MFUQueue
	 * @param string $queueID
	 * @return MFUQueue
	 */
	public function createQueueObj($queueID)
	{
		return new MFUQueue($this, $this->getEm(), $queueID, $this->tempPath);
	}

	/**
	 * Executes cleanup
	 */
	public function cleanup()
	{
		$this->getEm()->beginTransaction();
		foreach ($this->getQueues() as $queue) {
			if ($queue->getLastAccess() < time() - $this->lifeTime) {
				$queue->delete();
			}
		}
		$this->getEm()->commit();
	}

	/**
	 * gets all queues
	 *
	 * @return array
	 */
	public function getQueues()
	{
		$ret = array();
		$pairs = $this->getPairs('queueID', 'queueID');
		foreach ($pairs as $q) {
			$ret[] = $this->createQueueObj($q);
		}
		return $ret;
	}

}
