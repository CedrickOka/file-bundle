<?php
namespace Oka\FileBundle\Service;

use Oka\FileBundle\Model\ImageManipulatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Oka\FileBundle\Model\ImageInterface;

/**
 * 
 * @author cedrick
 * 
 */
class UploadedImageManager
{
	/**
	 * @var array $thumbnailFactory
	 */
	protected $thumbnailFactory;
	
	/**
	 * @var string $thumbnailMode
	 */
	protected $thumbnailMode;
	
	/**
	 * @var integer $thumbnailQuality
	 */
	protected $thumbnailQuality;
	
	public function __construct(array $thumbnailFactory, $thumbnailMode, $thumbnailQuality)
	{
		$this->thumbnailFactory = $thumbnailFactory;
		$this->thumbnailMode = $thumbnailMode;
		$this->thumbnailQuality = $thumbnailQuality;
	}

	/**
	 * Find the dominant color of image
	 *
	 * @param string $path
	 * @param boolean $optimize
	 * @return string The dominant color of image in RGB
	 */
	public function findImageDominantColor($path, $optimize = true)
	{
		$image = new \Imagick($path);
		
		if ($optimize) {
			if ($image->getImageHeight() > 250 && $image->getImageWidth() > 250) {
				$image->resizeImage(250, 250, \Imagick::FILTER_GAUSSIAN, 1);
			}
		}
		
		$image->quantizeImage(1, \Imagick::COLORSPACE_RGB, 0, false, false);
		$image->setFormat('RGB');
		
		return substr(bin2hex($image), 0, 6);
	}

	/**
	 * @param ImageManipulatorInterface $entity
	 */
	public function buildThumbnails(ImageInterface $entity, $refresh = false)
	{
		$className = get_class($entity);
		
		if (!array_key_exists($className, $this->thumbnailFactory)) {
			return false;
		}
		
		$fs = new Filesystem();
		$thumbnailsBuilded = [];
		
		foreach ($this->thumbnailFactory[$className] as $factory) {
			$mode = $factory['mode'] === null ? $this->thumbnailMode : $factory['mode'];
			$quality = $factory['quality'] === null ? $this->thumbnailQuality : $factory['quality'];
			$realPath = $entity->getRealPathFor($factory['width'], $factory['height'], $mode, $quality);
			
			// TODO Check if the thumbnails already exists
			if (!$fs->exists($realPath)) {
				if ($entity instanceof ImageManipulatorInterface) {
					$entity->thumbnail($factory['width'], $factory['height'], $mode, $quality);
					$thumbnailsBuilded[] = $realPath;
				}
			}
		}
		
		return $thumbnailsBuilded;
	}
}