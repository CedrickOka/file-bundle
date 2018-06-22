<?php
namespace Oka\FileBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
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
		$object = $arg->getObject();
		
		if ($object instanceof ImageInterface) {
			$object->setThumbnailMode($this->thumbnailMode);
			$object->setThumbnailQuality($this->thumbnailQuality);
			$this->handleUploadedImage($object);
		}
		
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function preUpdate(LifecycleEventArgs $arg)
	{
		$object = $arg->getObject();
		
		if ($object instanceof ImageInterface) {
			$this->handleUploadedImage($object);
		}		
	}
	
	/**
	 * @param LifecycleEventArgs $arg
	 */
	public function postLoad(LifecycleEventArgs $arg)
	{
		$object = $arg->getObject();
		
		if ($object instanceof ImageInterface) {
			$object->setThumbnailMode($this->thumbnailMode);
			$object->setThumbnailQuality($this->thumbnailQuality);
		}
	}
	
	public function getSubscribedEvents()
	{
		return [
				'prePersist',
				'preUpdate',
				'postLoad'
		];
	}
	
	/**
	 * @param ImageInterface $object
	 */
	private function handleUploadedImage(ImageInterface $object)
	{
		if (true === $object->hasUploadedFile()) {
			list($width, $height) = getimagesize($object->getUploadedFile()->getRealPath());
			$object->setHeight($height);
			$object->setWidth($width);
		}
	}
}
