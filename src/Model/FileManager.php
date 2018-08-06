<?php
namespace Oka\FileBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
abstract class FileManager implements FileManagerInterface
{
	/**
	 * @var string
	 */
	protected $class;
	
	/**
	 * @var ObjectManager
	 */
	protected $objectManager;
	
	/**
	 * @var \Doctrine\Common\Persistence\ObjectRepository
	 */
	protected $repository;
	
	/**
	 * Constructor.
	 * 
	 * @param ObjectManager	$om
	 * @param string		$class
	 */
	public function __construct(ObjectManager $om, $class)
	{
		$this->objectManager = $om;
		$this->setClass($class);
	}
	
	public function getClass()
	{
		return $this->class;
	}
	
	public function setClass($class)
	{
		$this->repository = $this->objectManager->getRepository($class);
		$this->class = $this->objectManager->getClassMetadata($class)->getName();
		return $this;
	}
	
	public function getObjectManager()
	{
		return $this->objectManager;
	}
	
	public function createFile()
	{
		$class = $this->getClass();
		$file = new $class();
		
		return $file;
	}
	
	public function updateFile(FileInterface $file, $andFlush = true)
	{
		if (!$this->objectManager->contains($file)) {
			$this->objectManager->persist($file);
		}
		
		if (true === $andFlush) {
			$this->objectManager->flush();
		}
		
		return $file;
	}
	
	public function deleteFile(FileInterface $file)
	{
		$this->objectManager->remove($file);
		$this->objectManager->flush($file);
	}
	
	public function findFile($id)
	{
		return $this->repository->find($id);
	}
	
	public function findFileBy(array $criteria)
	{
		return $this->repository->findOneBy($criteria);
	}
	
	public function findFilesBy(array $criteria, array $order = [], $limit = null, $offset = null)
	{
		return $this->repository->findBy($criteria, $order, $limit, $offset);
	}
	
	public function findFiles()
	{
		return $this->repository->findAll();
	}
}
