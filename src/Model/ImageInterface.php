<?php
namespace Oka\FileBundle\Model;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface ImageInterface
{
	/**
	 * Get the image width
	 * 
	 * @return int
	 */
	public function getWidth();
	
	/**
	 * Set the image width
	 * 
	 * @param int $width
	 */
	public function setWidth($width);
	
	/**
	 * Get the image height
	 * 
	 * @return int
	 */
	public function getHeight();
	
	/**
	 * Set the image height
	 * 
	 * @param int $height
	 */
	public function setHeight($height);
	
	/**
	 * Get the dominant color in RGB format
	 * 
	 * @return string
	 */
	public function getDominantColor();
	
	/**
	 * Set the dominant color in RGB format
	 * 
	 * @param string $colorRGB
	 */
	public function setDominantColor($colorRGB);
	
	/**
	 * Get the dominant color image placeholder
	 * 
	 * @return string
	 */
	public function getPlaceholder();
}
