<?php
namespace Oka\FileBundle\EventListener;

use Oka\FileBundle\OkaFileEvents;
use Oka\FileBundle\Event\UploadedFileEvent;
use Oka\FileBundle\Model\ImageInterface;
use Oka\FileBundle\Model\ImageManipulatorInterface;
use Oka\FileBundle\Service\ContainerParameterBag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class UploadedImageListener implements EventSubscriberInterface
{
	/**
	 * @var ImageManipulatorInterface $imageManipulator
	 */
	protected $imageManipulator;
	
	/**
	 * @var ContainerParameterBag $containerBag
	 */
	protected $containerBag;
	
	/**
	 * @var array $dominantColor
	 */
	protected $dominantColor;
	
	/**
	 * @var array $thumbnailFactory
	 */
	protected $thumbnailFactory;
	
	public function __construct(ImageManipulatorInterface $imageManipulator, ContainerParameterBag $containerBag, array $dominantColor, array $thumbnailFactory = [])
	{
		$this->imageManipulator = $imageManipulator;
		$this->containerBag = $containerBag;
		$this->dominantColor = $dominantColor;
		$this->thumbnailFactory = $thumbnailFactory;
	}
	
	public function onUploadedFileMoving(UploadedFileEvent $event)
	{
		$object = $event->getObject();
		
		if ($object instanceof ImageInterface) {
			$uploadedFile = $event->getUploadedFile();
			$container = $this->containerBag->get($object, ['dominant_color' => $this->dominantColor]);
			
			if (true === $container['dominant_color']['enabled']) {
				$object->setDominantColor($this->imageManipulator->getDominantColor($object, $container['dominant_color']['method'], $container['dominant_color']['options']));
			}
			
			list($width, $height) = getimagesize($uploadedFile->getRealPath());
			$object->setHeight($height);
			$object->setWidth($width);
		}
	}
	
	public function onUploadedFileMoved(UploadedFileEvent $event)
	{
		$object = $event->getObject();
		
		if ($object instanceof ImageInterface) {
			$container = $this->containerBag->get($object, ['thumbnail_factory' => $this->thumbnailFactory]);
			
			if (true === empty($container['thumbnail_factory'])) {
				return;
			}
			
			foreach ($container['thumbnail_factory'] as $value) {
				$this->imageManipulator->thumbnail($object, $value['width'], $value['height'], $value['method'], $value['quality']);
			}
		}
	}
	
	public static function getSubscribedEvents()
	{
		return [
				OkaFileEvents::UPLOADED_FILE_MOVING => 'onUploadedFileMoving',
				OkaFileEvents::UPLOADED_FILE_MOVED => 'onUploadedFileMoved'
		];
	}
}
