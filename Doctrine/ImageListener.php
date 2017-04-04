<?php
namespace Oka\FileBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Oka\FileBundle\Model\ImageInterface;

/**
 * 
 * @author cedrick
 * 
 */
class ImageListener implements EventSubscriber
{
	/**
	 * @var string $thumbnailMode
	 */
	protected $thumbnailMode;
	
	/**
	 * @var integer $thumbnailQuality
	 */
	protected $thumbnailQuality;
	
	public function __construct($thumbnailMode, $thumbnailQuality)
	{
		$this->thumbnailMode = $thumbnailMode;
		$this->thumbnailQuality = $thumbnailQuality;
	}
	
	public function prePersist(LifecycleEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof ImageInterface) {
			$entity->setThumbnailMode($this->thumbnailMode);
			$entity->setThumbnailQuality($this->thumbnailQuality);
			$this->handleUploadedImage($entity);
		}
		
	}
	
	public function preUpdate(PreUpdateEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof ImageInterface) {
			$this->handleUploadedImage($entity);
		}		
	}
	
	public function postLoad(LifecycleEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof ImageInterface) {
			$entity->setThumbnailMode($this->thumbnailMode);
			$entity->setThumbnailQuality($this->thumbnailQuality);
		}
	}
	
	public function getSubscribedEvents()
	{
		return [
				Events::prePersist,
				Events::preUpdate,
				Events::postLoad
		];
	}
	
	private function handleUploadedImage($entity)
	{
		if (true === $entity->hasUploadedFile()) {
			list($width, $height) = getimagesize($entity->getUploadedFile()->getRealPath());
			$entity->setHeight($height);
			$entity->setWidth($width);
		}
	}
}