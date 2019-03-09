<?php
namespace Oka\FileBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
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
	 * @param FileStorageHandlerInterface $fileStorageHandler
	 * @param EventDispatcherInterface $dispatcher
	 */
	public function __construct(FileStorageHandlerInterface $fileStorageHandler, EventDispatcherInterface $dispatcher)
	{
		$this->fileStorageHandler = $fileStorageHandler;
		$this->dispatcher = $dispatcher;
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
			
			$this->fileStorageHandler->open();
			$this->dispatcher->dispatch(OkaFileEvents::UPLOADED_FILE_MOVING, new UploadedFileEvent($object, $object->getUploadedFile()));			
			$object->setLastModified();
			var_dump($object->getUploadedFile());
		}
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function postPersist(LifecycleEventArgs $arg)
	{
		$object = $arg->getObject();
		
		if ($object instanceof FileInterface) {
			$this->moveFile($object, false);
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
			$this->moveFile($object);
		}
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function postRemove(LifecycleEventArgs $arg)
	{
		$object = $arg->getObject();
		
		if ($object instanceof FileInterface) {
			$this->fileStorageHandler->remove($object);
		}
	}
	
	public function getSubscribedEvents()
	{
		return [
				Events::prePersist,
				Events::postPersist,
				Events::preUpdate,
				Events::postUpdate,
				Events::postRemove
		];
	}
	
	/**
	 * @param FileInterface $file
	 * @param bool $andRemove
	 */
	private function moveFile(FileInterface $file, $andRemove = true)
	{
		if (false === $file->hasUploadedFile()) {
			return;
		}
		
		if ($uploadedFile = $this->fileStorageHandler->move($file, $andRemove)) {
			$this->dispatcher->dispatch(OkaFileEvents::UPLOADED_FILE_MOVED, new UploadedFileEvent($file, $uploadedFile));
		}
	}
}
