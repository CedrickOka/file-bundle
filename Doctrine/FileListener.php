<?php
namespace Oka\FileBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Oka\FileBundle\Event\UploadedFileEvent;
use Oka\FileBundle\Model\FileInterface;
use Oka\FileBundle\OkaFileEvents;
use Oka\FileBundle\Utils\FileUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class FileListener implements EventSubscriber
{
	/**
	 * @var EventDispatcherInterface $dispatcher
	 */
	protected $dispatcher;
	
	/**
	 * @var string $rootPath
	 */
	protected $rootPath;
	
	/**
	 * @var array $dataDirnames
	 */
	protected $dataDirnames;
	
	/**
	 * @var array $entityDirnames
	 */
	protected $entityDirnames;
	
	/**
	 * @var string $host
	 */
	protected $host;
	
	/**
	 * @var integer $port
	 */
	protected $port;
	
	/**
	 * @var boolean $secure
	 */
	protected $secure;
	
	/**
	 * @var Filesystem $fs
	 */
	protected $fs;
	
	/**
	 * @var string $systemOwner
	 */
	protected $systemOwner;
	
	/**
	 * @param EventDispatcherInterface $dispatcher
	 * @param string $rootPath
	 * @param array $dataDirnames
	 * @param array $entityDirnames
	 * @param string $host
	 * @param integer $port
	 * @param boolean $secure
	 */
	public function __construct(EventDispatcherInterface $dispatcher, $rootPath, array $dataDirnames, array $entityDirnames, $host, $port, $secure) {
		$this->dispatcher = $dispatcher;
		$this->rootPath = $rootPath;
		$this->dataDirnames = $dataDirnames;
		$this->entityDirnames = $entityDirnames;
		$this->host = $host;
		$this->port = $port;
		$this->secure = $secure;
		$this->fs = new Filesystem();
		$this->systemOwner = FileUtil::getSystemOwner();
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function prePersist(LifecycleEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof FileInterface) {
			if (false === $entity->hasUploadedFile()) {
				throw new \LogicException('It is not possible to persist a file entity without attaching it to an UploadedFile object.');
			}
			
			$classMetadata = $arg->getEntityManager()->getClassMetadata(get_class($entity));
			$this->loadContainerConfig($entity, $classMetadata);
			$path = FileUtil::findParentDirectoyThatExists($entity->getPath());
			
			if (!is_writable($path)) {
				throw new FileException(sprintf('Unable to write in the "%s" directory', $path));
			}
			
			$this->dispatcher->dispatch(OkaFileEvents::UPLOADED_FILE_MOVING, new UploadedFileEvent($entity, $entity->getUploadedFile()));
			$entity->setLastModified();
		}
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function postPersist(LifecycleEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof FileInterface) {
			$this->handleMoveFile($entity);
		}
	}
	
	/**
	 * @param PreUpdateEventArgs $arg
	 */
	public function preUpdate(PreUpdateEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof FileInterface) {
			if (true === $entity->hasUploadedFile()) {
				$this->dispatcher->dispatch(OkaFileEvents::UPLOADED_FILE_MOVING, new UploadedFileEvent($entity, $entity->getUploadedFile()));
				$entity->setLastModified();
			}
		}
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function postUpdate(LifecycleEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof FileInterface) {
			if (true === $entity->hasUploadedFile()) {
				$this->handleMoveFile($entity);
			}
		}
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function preRemove(LifecycleEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof FileInterface) {
			$entity->removeFile();
		}
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function postLoad(LifecycleEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof FileInterface) {
			$classMetadata = $arg->getEntityManager()->getClassMetadata(get_class($entity));
			$this->loadContainerConfig($entity, $classMetadata);
		}
	}
	
	public function getSubscribedEvents()
	{
		return [
				Events::prePersist,
				Events::postPersist,
				Events::preUpdate,
				Events::postUpdate,
				Events::preRemove,
				Events::postLoad
		];
	}
	
	/**
	 * @param FileInterface $entity
	 * @param ClassMetadata $classMetadata
	 */
	private function loadContainerConfig(FileInterface $entity, ClassMetadata $classMetadata)
	{
		$entity->setRootPath($this->rootPath);
		$entity->setHost($this->host);
		$entity->setPort($this->port);
		$entity->setSecure($this->secure);
		$entity->setFileSystem($this->fs);
		$entity->setSystemOwner($this->systemOwner);
		
		$dirname = null;
		$entityClass = $classMetadata->getName();
		
		foreach ($this->dataDirnames as $key => $value) {
			$className = 'Oka\FileBundle\Model\\'.ucfirst($key).'Interface';
			
			if ($entity instanceof $className) {
				$dirname = $value;
				break;
			}
		}
		
		if (isset($this->entityDirnames[$entityClass])) {
			$dirname = $dirname !== null ? $dirname . '/' . $this->entityDirnames[$entityClass] : $this->entityDirnames[$entityClass];
		}
		$entity->setDirname($dirname);
	}
	
	/**
	 * @param FileInterface $entity
	 */
	private function handleMoveFile(FileInterface $entity)
	{
		$uploadedFile = $entity->moveFile();
		$this->dispatcher->dispatch(OkaFileEvents::UPLOADED_FILE_MOVED, new UploadedFileEvent($entity, $uploadedFile));
	}
}
