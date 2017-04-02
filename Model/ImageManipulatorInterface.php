<?php
namespace Oka\FileBundle\Model;

/**
 * 
 * @author cedrick
 * 
 */
interface ImageManipulatorInterface
{
	/**
	 * Thumbnailize image
	 * 
	 * @param integer $width
	 * @param integer $height
	 * @param string $mode
	 * @param integer $quality
	 */
	public function thumbnail($width = null, $height = null, $mode = null, $quality = null);
	
	/**
	 * Crop image
	 * 
	 * @param integer $x0
	 * @param integer $y0
	 * @param integer $x1
	 * @param integer $y1
	 * @param string $destination
	 * @param string $format
	 */
	public function crop($x0, $y0, $x1, $y1, $destination = null, $format = null);
}