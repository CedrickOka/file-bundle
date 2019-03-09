<?php
namespace Oka\FileBundle\Service;

use Imagine\Image\Box;
use Imagine\Image\Point;
use Oka\FileBundle\Model\FileInterface;
use Oka\FileBundle\Model\FileStorageHandlerInterface;
use Oka\FileBundle\Model\ImageInterface;
use Oka\FileBundle\Model\ImageManipulatorInterface;
use Oka\FileBundle\Util\KmeansImage;

/**
 *
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class ImageManipulator implements ImageManipulatorInterface
{
	const DOMINANT_COLOR_METHOD_KMEANS = 'k-means';
	const DOMINANT_COLOR_METHOD_QUANTIZE = 'quantize';
	
	/**
	 * @var FileStorageHandlerInterface $fileStorageHandler
	 */
	protected $fileStorageHandler;
	
	public function __construct(FileStorageHandlerInterface $fileStorageHandler)
	{
		$this->fileStorageHandler = $fileStorageHandler;
	}
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\ImageManipulatorInterface::getDominantColor()
	 */
	public function getDominantColor(ImageInterface $image, $method = 'quantize', array $options = [], $optimize = true) {
		if (null !== $method && false === in_array($method, [self::DOMINANT_COLOR_METHOD_KMEANS, self::DOMINANT_COLOR_METHOD_QUANTIZE])) {
			throw new \InvalidArgumentException(sprintf('Arguments "$method" have not valid value "%s"', $method));
		}
		
		$realPath = $this->getRealPath($image);
		
		if (false === file_exists($realPath)) {
			throw new \LogicException(sprintf('No image found from the path "%s".', $realPath));
		}
		
		$img = new \Imagick($realPath);
		
		if (true === $optimize) {
			if ($img->getImageHeight() > 250 && $img->getImageWidth() > 250) {
				$img->resizeImage(250, 250, \Imagick::FILTER_GAUSSIAN, 1);
			}
		}
		
		switch (true) {
			case self::DOMINANT_COLOR_METHOD_QUANTIZE === $method:
				$img->quantizeImage(1, \Imagick::COLORSPACE_RGB, 0, false, false);
				$img->setformat('RGB');
				
				return substr(bin2hex($img), 0, 6);
				
			case self::DOMINANT_COLOR_METHOD_KMEANS === $method:
				$kmeans = new KmeansImage($img);
				
				if (false === empty($options)) {
					if (true === isset($options['ignoreExtremity']) && $options['ignoreExtremity']) {
						$kmeans->ignoreExtremity(true);
					}
					if (true === isset($options['ignoreExtremity']) && $options['ignoreExtremity']) {
						$kmeans->ignoreExtremity(true);
					}
					if (true === isset($options['blackLevel'])) {
						$kmeans->setBlackLevel($options['blackLevel']);
					}
					if (true === isset($options['whiteLevel'])) {
						$kmeans->setWhiteLevel($options['whiteLevel']);
					}
				}
				$kmeans->execute();
				$centroid = $kmeans->getDominantCentroid();
				
				return $centroid['hex'];
				
			default:
				return null;
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\ImageManipulatorInterface::thumbnail()
	 */
	public function thumbnail(ImageInterface $image, $width = null, $height = null, $mode = 'ratio', $quality = 100)
	{
		if (null === $height && null === $width) {
			throw new \LogicException('You must at least specify a size to create a thumbnail.');
		}
		
		$realPath = $this->getRealPath($image);
		
		if (false === file_exists($realPath)) {
			throw new \LogicException(sprintf('No image found from the path "%s".', $realPath));
		}
		
		/** @var \Imagine\Image\ImageInterface $img */
		$imagine = static::createImagine();
		/** @var \Imagine\Image\ImageInterface $img */
		$img = $imagine->open($realPath);
		$box = $img->getSize();
		
		if (null !== $height && null !== $width) {
			switch ($mode) {
				case 'ratio':
					$img->resize(($width / $height) <= ($box->getWidth() / $box->getHeight()) ? $box->widen($width) : $box->heighten($height));
					break;
					
				case 'inset':
					$img = $img->thumbnail(new Box($width, $height), \Imagine\Image\ImageInterface::THUMBNAIL_INSET);
					break;
					
				case 'outbound':
					$img = $img->thumbnail(new Box($width, $height), \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND);
					break;
			}
		} elseif (null === $height && null !== $width && $box->getWidth() > $width) {
			$img->resize($box->widen($width));
		} elseif (null !== $height && null === $width && $box->getHeight() > $height) {
			$img->resize($box->heighten($height));
		}
		
		$path = $this->fileStorageHandler->getThumbnailPath($image, $mode, $quality, $width, $height);
		
		if (false === file_exists($path)) {
			mkdir($path, 0755, true);
		}
		
		$img->save($this->fileStorageHandler->getThumbnailRealPath($image, $mode, $quality, $width, $height), ['quality' => $quality]);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\ImageManipulatorInterface::crop()
	 */
	public function crop(ImageInterface $image, $x0, $y0, $x1, $y1, $destination = null, $format = null)
	{
		if ($x1 < $x0 || $y1 < $y0) {
			throw new \LogicException('The coordinates "x1" and "y1" must be greater than "x0" and "y0", respectively.');
		}
		
		$realPath = $this->getRealPath($image);
		
		if (false === file_exists($realPath)) {
			throw new \LogicException(sprintf('No image found from the path "%s".', $realPath));
		}
		
		$imagine = new \Imagine\Imagick\Imagine();
		/** @var \Imagine\Image\ImageInterface $img */
		$img = $imagine->open($realPath);
		$img->crop(new Point($x0, $y0), new Box(($x1 - $x0), ($y1 - $y0)));
		
		if (null === $destination) {
			$this->fileStorageHandler->remove($image);
		}
		
		$img->save($destination ?: $realPath, null === $format ? [] : ['format' => $format]);
	}
	
	/**
	 * @throws \RuntimeException
	 * @return \Imagick|\Gmagick
	 */
	public static function createImagick() {
		if (true === class_exists('Imagick')) {
			return new \Imagick;
		}
		
		if (true === class_exists('Gmagick')) {
			return new \Gmagick;
		}
		
		throw new \RuntimeException('Unable to load Imagick or Gmagick class');
	}
	
	/**
	 * Create Imagine class instance
	 * 
	 * @return \Imagine\Image\ImagineInterface
	 */
	public static function createImagine() {
		if (true === class_exists('Imagick')) {
			return new \Imagine\Imagick\Imagine();
		}
		
		if (true === class_exists('Gmagick')) {
			return new \Imagine\Gmagick\Imagine();
		}
		
		return new \Imagine\Gd\Imagine();
	}
	
	protected function getRealPath(ImageInterface $image)
	{
		return $image instanceof FileInterface && true === $image->hasUploadedFile() ? $image->getUploadedFile()->getRealPath() : $this->fileStorageHandler->getRealPath($image);
	}
}
