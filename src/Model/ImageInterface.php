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
	 * @return integer
	 */
	public function getWidth();
	
	/**
	 * @param integer $width
	 */
	public function setWidth($width);
	
	/**
	 * @return integer
	 */
	public function getHeight();
	
	/**
	 * @param integer $height
	 */
	public function setHeight($height);
	
	/**
	 * @return string
	 */
	public function getDominantColor();
	
	/**
	 * @return string
	 */
	public function getPlaceholder();
	
	/**
	 * @param string $colorRGB
	 */
	public function setDominantColor($colorRGB);
	
	public function setThumbnailMode($thumbnailMode);
	
	public function setThumbnailQuality($thumbnailQuality);
	
	public static function createDirnameWith($mode, $quality, $width = null, $height = null);
	
	public function getUriFor($width = null, $height = null, $mode = null, $quality = null);
	
	public function getPathFor($width = null, $height = null, $mode = null, $quality = null);
	
	public function getRealPathFor($width = null, $height = null, $mode = null, $quality = null);
	
	public function getRealPathsFor($width = null, $height = null, $mode = null, $quality = null);
	
	public function removeFileFor($width = null, $height = null, $mode = null, $quality = null);
}
