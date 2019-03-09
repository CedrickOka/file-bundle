<?php
namespace Oka\FileBundle\Model;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface ImageManipulatorInterface
{
	/**
	 * Gets image dominant color
	 * 
	 * @param ImageInterface $image
	 * @param string $method
	 * @param array $options
	 * @param boolean $optimize
	 * @return string
	 */
	public function getDominantColor(ImageInterface $image, $method = 'quantize', array $options = [], $optimize = true);
	
	/**
	 * Thumbnailize image
	 * 
	 * @param ImageInterface $image
	 * @param string $mode
	 * @param int $quality
	 * @param int $width
	 * @param int $height
	 */
	public function thumbnail(ImageInterface $image, $width = null, $height = null, $mode = 'ratio', $quality = 100);
	
	/**
	 * Crop image
	 * 
	 * @param ImageInterface $image
	 * @param int $x0
	 * @param int $y0
	 * @param int $x1
	 * @param int $y1
	 * @param string $destination
	 * @param string $format
	 */
	public function crop(ImageInterface $image, $x0, $y0, $x1, $y1, $destination = null, $format = null);
}
