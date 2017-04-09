<?php
namespace Oka\FileBundle\DoctrineBehaviors\Model\PictureCoverable;

use Oka\FileBundle\Model\Image;

/**
 * 
 * @author cedrick
 * 
 */
trait PictureCoverable
{	
	/**
	 * @var Image $pictureCover
	 */
	protected $pictureCover;
	
	/**
	 * @return Image
	 */
	public function getPictureCover() {
		return $this->pictureCover;
	}
	
	/**
	 * @param Image $pictureCover
	 */
	public function setPictureCover(Image $pictureCover) {
		$this->pictureCover = $pictureCover;
		return $this;
	}
}