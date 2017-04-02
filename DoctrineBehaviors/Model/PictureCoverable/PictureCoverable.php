<?php
namespace Oka\FileBundle\DoctrineBehaviors\Model\PictureCoverable;

use Oka\FileBundle\Model\ImageInterface;

/**
 * 
 * @author cedrick
 * 
 */
trait PictureCoverable
{	
	/**
	 * @var ImageInterface $pictureCover
	 */
	protected $pictureCover;
	
	/**
	 * @return ImageInterface
	 */
	public function getPictureCover() {
		return $this->pictureCover;
	}
	
	/**
	 * @param ImageInterface $pictureCover
	 */
	public function setPictureCover(ImageInterface $pictureCover) {
		$this->pictureCover = $pictureCover;
		return $this;
	}
}