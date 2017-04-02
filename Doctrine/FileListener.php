<?php
namespace Oka\FileBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Oka\FileBundle\Event\UploadedFileEvent;
use Oka\FileBundle\Model\FileInterface;
use Oka\FileBundle\OkaFileEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * 
 * @author cedrick
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
	
	public function __construct(EventDispatcherInterface $dispatcher, $rootPath, array $dataDirnames, $host, $port, $secure) {
		$this->dispatcher = $dispatcher;
		$this->rootPath = $rootPath;
		$this->dataDirnames = $dataDirnames;
		$this->host = $host;
		$this->port = $port;
		$this->secure = $secure;
		
		$this->fs = new Filesystem();
		$this->systemOwner = exec('ps axo user,comm | grep -E \'[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx\' | grep -v root | head -1 | cut -d\  -f1');
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function prePersist(LifecycleEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof FileInterface) {
			if (false == $entity->hasUploadedFile()) {
				throw new \LogicException('It is not possible to persist a file entity without attaching it to an UploadedFile object.');
			}
			
			$this->loadContainerConfig($entity);
			
			if (!is_writable($entity->getPath())) {
				throw new FileException(sprintf('Unable to write in the "%s" directory', $entity->getPath()));
			}
			
			$this->dispatcher->dispatch(OkaFileEvents::UPLOADED_FILE_MOVING, new UploadedFileEvent($entity, $entity->getUploadedFile()));
			$entity->setLastModified();
		}
	}
	
	public function postPersist(LifecycleEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof FileInterface) {
			$this->handleMoveFile($entity);
		}
	}
	
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
	
	public function postUpdate(LifecycleEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof FileInterface) {
			if (true === $entity->hasUploadedFile()) {
				$this->handleMoveFile($entity);
			}
		}
	}
	
	public function preRemove(LifecycleEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof FileInterface) {
			$entity->removeFile();
		}
	}
	
	public function postLoad(LifecycleEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof FileInterface) {
			$this->loadContainerConfig($entity);
		}
	}
	
	protected function loadContainerConfig(FileInterface $entity) {
		$entity->setRootPath($this->rootPath);
		$entity->setSecure($this->secure);
		$entity->setHost($this->host);
		$entity->setPort($this->port);
		$entity->setFileSystem($this->fs);
		$entity->setSystemOwner($this->systemOwner);
		
		foreach ($this->dataDirnames as $key => $dirname) {
			$className = 'Oka\FileBundle\Model\\'.ucfirst($key).'Interface';
			
			if ($entity instanceof $className) {
				$entity->setDirname($dirname);
				break;
			}
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
	
	protected function handleMoveFile(FileInterface $entity)
	{
		$uploadedFile = $entity->moveFile();
		$this->dispatcher->dispatch(OkaFileEvents::UPLOADED_FILE_MOVED, new UploadedFileEvent($entity, $uploadedFile));
	}
}