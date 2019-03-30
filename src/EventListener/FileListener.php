<?php
namespace Oka\FileBundle\EventListener;

use Doctrine\Common\EventArgs;
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
	 * @var array $filesToMove
	 */
	protected $filesToMove;
	
	/**
	 * @param FileStorageHandlerInterface $fileStorageHandler
	 * @param EventDispatcherInterface $dispatcher
	 */
	public function __construct(FileStorageHandlerInterface $fileStorageHandler, EventDispatcherInterface $dispatcher)
	{
		$this->fileStorageHandler = $fileStorageHandler;
		$this->dispatcher = $dispatcher;
		$this->fileHasMove = [];
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
			
			$this->fileHasMove['prePersist'][] = $object;
			$this->fileStorageHandler->open();
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
				$this->fileHasMove['preUpdate'][] = $object;
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
	
	/**
	 * @param EventArgs $arg
	 */
	public function postFlush(EventArgs $arg)
	{
		if (false === empty($this->fileHasMove)) {
			foreach ($this->fileHasMove as $key => $files) {
				foreach ($files as $file) {
					if ($uploadedFile = $this->fileStorageHandler->move($file, Events::preUpdate === $key)) {
						$this->dispatcher->dispatch(OkaFileEvents::UPLOADED_FILE_MOVED, new UploadedFileEvent($file, $uploadedFile));
					}
				}				
			}
		}
	}
	
	public function getSubscribedEvents()
	{
		return [
				Events::prePersist,
// 				Events::postPersist,
				Events::preUpdate,
// 				Events::postUpdate,
				Events::postRemove,
				Events::postFlush
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
