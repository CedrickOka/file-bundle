<?php
namespace Oka\FileBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Oka\FileBundle\OkaFileEvents;
use Oka\FileBundle\Event\UploadedFileEvent;
use Oka\FileBundle\Model\FileInterface;
use Oka\FileBundle\Model\FileStorageHandlerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class FileListener implements EventSubscriber
{
	/**
	 * @var FileStorageHandlerInterface $fileStorageHandler
	 */
	protected $fileStorageHandler;
	
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
	 * @var array $objectDirnames
	 */
	protected $objectDirnames;
	
	/**
	 * @var boolean $secure
	 */
	protected $secure;
	
	/**
	 * @var string $host
	 */
	protected $host;
	
	/**
	 * @var integer $port
	 */
	protected $port;
	
	/**
	 * @param FileStorageHandlerInterface $fileStorageHandler
	 * @param EventDispatcherInterface $dispatcher
	 * @param string $rootPath
	 * @param array $dataDirnames
	 * @param array $objectDirnames
	 * @param string $host
	 * @param integer $port
	 * @param boolean $secure
	 */
	public function __construct(FileStorageHandlerInterface $fileStorageHandler, EventDispatcherInterface $dispatcher, $rootPath, array $dataDirnames, array $objectDirnames, $secure, $host, $port) {
		$this->fileStorageHandler = $fileStorageHandler;
		$this->dispatcher = $dispatcher;
		$this->rootPath = $rootPath;
		$this->dataDirnames = $dataDirnames;
		$this->objectDirnames = $objectDirnames;
		$this->secure = $secure;
		$this->host = $host;
		$this->port = $port;
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function prePersist(LifecycleEventArgs $arg)
	{
		$object = $arg->getObject();
		
		if ($object instanceof FileInterface) {
			if (false === $object->hasUploadedFile()) {
				throw new \LogicException('It is not possible to persist a file object without attaching it to an UploadedFile object.');
			}
			
			$this->loadContainerConfig($object, $arg->getObjectManager()->getClassMetadata(get_class($object)));
			
			$this->fileStorageHandler->checkAccess($object);
			$this->dispatcher->dispatch(OkaFileEvents::UPLOADED_FILE_MOVING, new UploadedFileEvent($object, $object->getUploadedFile()));
			
			$object->setLastModified();
		}
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function postPersist(LifecycleEventArgs $arg)
	{
		$object = $arg->getObject();
		
		if ($object instanceof FileInterface) {
			$this->handleMoveFile($object);
		}
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function preUpdate(LifecycleEventArgs $arg)
	{
		$object = $arg->getObject();
		
		if ($object instanceof FileInterface) {
			if (true === $object->hasUploadedFile()) {
				$this->dispatcher->dispatch(OkaFileEvents::UPLOADED_FILE_MOVING, new UploadedFileEvent($object, $object->getUploadedFile()));
				$object->setLastModified();
			}
		}
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function postUpdate(LifecycleEventArgs $arg)
	{
		$object = $arg->getObject();
		
		if ($object instanceof FileInterface) {
			if (true === $object->hasUploadedFile()) {
				$this->handleMoveFile($object);
			}
		}
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function preRemove(LifecycleEventArgs $arg)
	{
		$object = $arg->getObject();
		
		if ($object instanceof FileInterface) {
			$object->removeFile();
		}
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function postLoad(LifecycleEventArgs $arg)
	{
		$object = $arg->getObject();
		
		if ($object instanceof FileInterface) {
			$this->loadContainerConfig($object, $arg->getObjectManager()->getClassMetadata(get_class($object)));
		}
	}
	
	public function getSubscribedEvents()
	{
		return [
				'prePersist',
				'postPersist',
				'preUpdate',
				'postUpdate',
				'preRemove',
				'postLoad'
		];
	}
	
	/**
	 * @param FileInterface $object
	 * @param ClassMetadata $classMetadata
	 */
	private function loadContainerConfig(FileInterface $object, ClassMetadata $classMetadata)
	{
		$object->setHost($this->host);
		$object->setPort($this->port);
		$object->setSecure($this->secure);
		$object->setRootPath($this->rootPath);
		
		$dirname = null;
		$objectClass = $classMetadata->getName();
		
		foreach ($this->dataDirnames as $key => $value) {
			$className = 'Oka\FileBundle\Model\\'.ucfirst($key).'Interface';
			
			if ($object instanceof $className) {
				$dirname = $value;
				break;
			}
		}
		
		if (isset($this->objectDirnames[$objectClass])) {
			$dirname = $dirname !== null ? $dirname . '/' . $this->objectDirnames[$objectClass] : $this->objectDirnames[$objectClass];
		}
		
		$object->setDirname($dirname);
	}
	
	/**
	 * @param FileInterface $object
	 */
	private function handleMoveFile(FileInterface $object)
	{
		if ($uploadedFile = $this->fileStorageHandler->save($object)) {
			$this->dispatcher->dispatch(OkaFileEvents::UPLOADED_FILE_MOVED, new UploadedFileEvent($object, $uploadedFile));
		}
	}
}
