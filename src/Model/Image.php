<?php
namespace Oka\FileBundle\Model;

/**
 *
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
abstract class Image extends File implements ImageInterface
{
	/**
	 * @var integer $width
	 */
	protected $width;
	
	/**
	 * @var integer $height
	 */
	protected $height;
	
	/**
	 * @var string $dominantColor
	 */
	protected $dominantColor;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->width = 0;
		$this->height = 0;
		$this->dominantColor = 'ffffff';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\ImageInterface::getWidth()
	 */
	public function getWidth()
	{
		return $this->width;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\ImageInterface::setWidth()
	 */
	public function setWidth($width)
	{
		$this->width = $width;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\ImageInterface::getHeight()
	 */
	public function getHeight()
	{
		return $this->height;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\ImageInterface::setHeight()
	 */
	public function setHeight($height)
	{
		$this->height = $height;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\ImageInterface::getDominantColor()
	 */
	public function getDominantColor()
	{
		return $this->dominantColor;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\ImageInterface::setDominantColor()
	 */
	public function setDominantColor($dominantColor)
	{
		$this->dominantColor = $dominantColor;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\ImageInterface::getPlaceholder()
	 */
	public function getPlaceholder()
	{
		return static::createImageGIFPlaceholder($this->dominantColor);
	}
	
	public static function createGIFPlaceholder($color)
	{
		$header 					= '474946383961';
		$logicalScreenDescriptor 	= '01000100800100';
		$imageDescriptor 			= '2c000000000100010000';
		$imageData 					= '0202440100';
		// If you want to define trailer, define it with the value '3b';
		
		$gif = implode([
				$header,
				$logicalScreenDescriptor,
				$color,
				'000000',
				$imageDescriptor,
				$imageData,
				// Add the trailer value here
		]);
		
		return 'data:image/gif;base64,' . base64_encode(hex2bin($gif));
	}
}
