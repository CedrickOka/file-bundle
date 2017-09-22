<?php
namespace Oka\FileBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Oka\FileBundle\Model\ImageInterface;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
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
	
	/**
	 * @param string $thumbnailMode
	 * @param integer $thumbnailQuality
	 */
	public function __construct($thumbnailMode, $thumbnailQuality)
	{
		$this->thumbnailMode = $thumbnailMode;
		$this->thumbnailQuality = $thumbnailQuality;
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function prePersist(LifecycleEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof ImageInterface) {
			$entity->setThumbnailMode($this->thumbnailMode);
			$entity->setThumbnailQuality($this->thumbnailQuality);
			$this->handleUploadedImage($entity);
		}
		
	}
	
	/**
	 * @param PreUpdateEventArgs $arg
	 */
	public function preUpdate(PreUpdateEventArgs $arg)
	{
		$entity = $arg->getEntity();
		
		if ($entity instanceof ImageInterface) {
			$this->handleUploadedImage($entity);
		}		
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
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
	
	/**
	 * @param ImageInterface $entity
	 */
	private function handleUploadedImage(ImageInterface $entity)
	{
		if (true === $entity->hasUploadedFile()) {
			list($width, $height) = getimagesize($entity->getUploadedFile()->getRealPath());
			$entity->setHeight($height);
			$entity->setWidth($width);
		}
	}
}
