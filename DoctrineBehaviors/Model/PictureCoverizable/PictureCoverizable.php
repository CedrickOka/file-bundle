<?php
namespace Oka\FileBundle\DoctrineBehaviors\Model\PictureCoverizable;

use Oka\FileBundle\Model\ImageInterface;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
trait PictureCoverizable
{
	/**
	 * @var ImageInterface $pictureCover
	 */
	protected $pictureCover;
	
	/**
	 * @return ImageInterface
	 */
	public function getPictureCover()
	{
		return $this->pictureCover;
	}
	
	/**
	 * @param ImageInterface $pictureCover
	 */
	public function setPictureCover(ImageInterface $pictureCover)
	{
		$this->pictureCover = $pictureCover;
		return $this;
	}
	
	/**
	 * @param ImageInterface $pictureCover
	 */
	public function removePictureCover()
	{
		$this->pictureCover = null;
		return $this;
	}
}
