<?php
namespace Oka\FileBundle\EventListener;

use Oka\FileBundle\Event\UploadedFileEvent;
use Oka\FileBundle\Model\ImageInterface;
use Oka\FileBundle\Model\ImageManipulatorInterface;
use Oka\FileBundle\OkaFileEvents;
use Oka\FileBundle\Service\UploadedImageManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class UploadedImageListener implements EventSubscriberInterface
{
	/**
	 * @var UploadedImageManager $uploadedImageManager
	 */
	protected $uploadedImageManager;
	
	/**
	 * @var array $detectDominantColor
	 */
	protected $detectDominantColor;
	
	public function __construct(UploadedImageManager $uploadedImageManager, array $detectDominantColor)
	{
		$this->uploadedImageManager = $uploadedImageManager;
		$this->detectDominantColor = $detectDominantColor;
	}
	
	public function onUploadedFileMoving(UploadedFileEvent $event)
	{
		$entity = $event->getEntity();
		
		if ($entity instanceof ImageInterface && true === $this->detectDominantColor['enabled']) {
			$realPath = $event->getUploadedFile()->getRealPath();
			$entity->setDominantColor($this->uploadedImageManager->findImageDominantColor($realPath, $this->detectDominantColor['method'], $this->detectDominantColor['options']));
		}
	}
	
	public function onUploadedFileMoved(UploadedFileEvent $event)
	{
		$entity = $event->getEntity();
		
		if ($entity instanceof ImageManipulatorInterface) {
			$this->uploadedImageManager->buildThumbnails($entity);
		}
	}
	
	public static function getSubscribedEvents()
	{
		return [
				OkaFileEvents::UPLOADED_FILE_MOVING => 'onUploadedFileMoving',
				OkaFileEvents::UPLOADED_FILE_MOVED => ['onUploadedFileMoved', 5]
		];
	}
}
