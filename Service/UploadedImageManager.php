<?php
namespace Oka\FileBundle\Service;

use Oka\FileBundle\Model\ImageInterface;
use Oka\FileBundle\Model\ImageManipulatorInterface;
use Oka\FileBundle\Utils\KmeansImage;
use Symfony\Component\Filesystem\Filesystem;

/**
 * 
 * @author cedrick
 * 
 */
class UploadedImageManager
{
	const DOMINANT_COLOR_METHOD_KMEANS = 'k-means';
	const DOMINANT_COLOR_METHOD_QUANTIZE = 'quantize';
	
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
	 * @param string $method
	 * @param boolean $optimize
	 * @return string The dominant color of image in RGB
	 */
	public function findImageDominantColor($path, $method = null, array $options = [], $optimize = true)
	{
		if ($method !== null && !in_array($method, [self::DOMINANT_COLOR_METHOD_KMEANS, self::DOMINANT_COLOR_METHOD_QUANTIZE])) {
			throw new \InvalidArgumentException(sprintf('Arguments "$method" have not valid value "%s"', $method));
		}
		
		return self::DOMINANT_COLOR_METHOD_KMEANS === $method ? 
				$this->findDominantColorWithKmeans($path, $options, $optimize) : 
				$this->findDominantColorWithQuantize($path, $options, $optimize);
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
	
	private function findDominantColorWithQuantize($path, array $options = [], $optimize = true)
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
	
	private function findDominantColorWithKmeans($path, array $options = [], $optimize = true)
	{
		$image = new \Imagick($path);
		
		if ($optimize) {
			if ($image->getImageHeight() > 100 && $image->getImageWidth() > 100) {
				$image->resizeImage(100, 100, \Imagick::FILTER_GAUSSIAN, 1);
			}
		}
		
		$kmeans = new KmeansImage($image);
		
		if (!empty($options)) {
			if (isset($options['ignoreExtremity']) && $options['ignoreExtremity']) {
				$kmeans->ignoreExtremity(true);
			}
			
			if (isset($options['ignoreExtremity']) && $options['ignoreExtremity']) {
				$kmeans->ignoreExtremity(true);
			}
			
			if (isset($options['blackLevel'])) {
				$kmeans->setBlackLevel($options['blackLevel']);
			}
			
			if (isset($options['whiteLevel'])) {
				$kmeans->setWhiteLevel($options['whiteLevel']);
			}
		}
		
		$kmeans->execute();
		$centroid = $kmeans->getDominantCentroid();
		
		return $centroid['hex'];
	}
}