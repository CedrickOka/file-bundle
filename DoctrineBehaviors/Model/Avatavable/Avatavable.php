<?php
namespace Oka\FileBundle\DoctrineBehaviors\Model\Avatavable;

use Oka\FileBundle\Model\ImageInterface;

/**
 * 
 * @author cedrick
 * 
 */
trait Avatavable
{
	/**
	 * @var ImageInterface $avatar
	 */
	protected $avatar;
	
	/**
	 * @return boolean
	 */
	public function hasAvatar()
	{
		return $this->avatar !== null;
	}
	
	/**
	 * @return ImageInterface
	 */
	public function getAvatar()
	{
		return $this->avatar;
	}
	
	/**
	 * @param ImageInterface $avatar
	 */
	public function setAvatar(ImageInterface $avatar)
	{
		$this->avatar = $avatar;
		return $this;
	}
}