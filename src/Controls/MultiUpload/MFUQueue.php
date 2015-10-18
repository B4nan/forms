<?php

namespace Bargency\Forms\Controls\MultiUpload;

use Bargency\Entities\Admin\MfuFile;
use Kdyby\Doctrine\EntityManager;
use Nette\Object,
	Nette\Http\FileUpload;

/**
 * MultiUpload queue model
 *
 * @author Martin Adámek <adamek@bargency.com>
 */
class MFUQueue extends Object
{

	/** @var Model */
	private $model;

	/** @var EntityManager */
	private $em;

	/** @var string */
	private $queueID;

	/** @var string */
	private $tempPath;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $repository;

	public function __construct($model, EntityManager $em, $queueID, $tempPath)
	{
		$this->model = $model;
		$this->em = $em;
		$this->queueID = $queueID;
		$this->tempPath = $tempPath;
		$this->repository = $em->getRepository(MfuFile::class);
	}

	/**
	 * Adds file to queue
	 *
	 * @param FileUpload $file
	 */
	public function addFile(FileUpload $file)
	{
		$uid = uniqid();
		$path = "$this->tempPath/upload-$this->queueID-$uid.tmp";
		$file->move($path);
		$f = new MfuFile([
			'queueID' => $this->queueID,
			'data' => base64_encode(serialize($file)),
			'name' => $file->name,
		]);
		$this->em->persist($f);
		$this->em->flush($f);
	}

	public function addFileManually($name, $token, $chunk, $chunks)
	{
		$f = new MfuFile([
			'queueID' => $this->queueID,
			'created' => time(),
			'name'    => $name,
			'fileID'  => $token,
			'chunk'   => $chunk,
			'chunks'  => $chunks,
			'data'    => '',
		]);
		$this->em->persist($f);
		$this->em->flush($f);
	}

	public function updateFile($name, $chunk, FileUpload $file = NULL)
	{
		$where = array(
			'queueID' => $this->queueID,
			'name' => $name,
		);
		$res = $this->repository->findBy($where);
		foreach ($res as $f) {
			$f->chunk = $chunk;
			if ($file) {
				$f->data = base64_encode(serialize($file));
			}
			$this->em->persist($f);
		}
		$this->em->flush();
	}

	/**
	 * Gets files
	 * @return array of FileUpload
	 */
	public function getFiles($tokens = NULL, $limit = NULL)
	{
		$ret = array();

		$qb = $this->repository->createQueryBuilder('f');
		$qb->whereCriteria(['queueID' => $this->queueID]);
		if (is_array($tokens)) {
			$qb->andWhere('f.fileID IN (:tokens)')
                ->setParameter('tokens', $tokens);
		}
		$qb->setMaxResults($limit);

		$files = $qb->getQuery()->getResult();
		foreach ($files as $row) {
			$file = unserialize(base64_decode($row->data));
			if (!($file instanceof FileUpload)) {
				continue;
			}
			$ret[] = $file;
		}

		return $ret;
	}

	public function delete()
	{
		$dir = realpath($this->tempPath);
		foreach ($this->getFiles() as $file) {
			$fileDir = dirname($file->temporaryFile);
			if (realpath($fileDir) === $dir && file_exists($file->temporaryFile)) {
				@unlink($file->temporaryFile); // @ intentionally
			}
		}

		$f = $this->repository->findBy(['queueID' => $this->queueID]);

		$this->em->remove($f);
		$this->em->flush();
	}

	/**
	 * @return int timestamp
	 */
	public function getLastAccess()
	{
		$q = $this->repository->createQueryBuilder('f');
		$q->select('MAX(f.created)');
		$q->whereCriteria(['f.queueID' => $this->queueID]);
		$q->orderBy('f.created', 'DESC');
		$q->setMaxResults(1);
		return (int) $q->getQuery()->getSingleScalarResult();
	}

}
