<?php
namespace Oka\FileBundle\DoctrineBehaviors\Model\Avatarizable;

use Oka\FileBundle\Model\ImageInterface;

/**
 * 
 * @author cedrick
 * 
 */
trait Avatarizable
{
	/**
	 * @var ImageInterface $avatar
	 */
	protected $avatar;
	
	/**
	 * @var string $defaultUri
	 */
	protected $defaultUri;
	
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
	 * @return \Oka\FileBundle\DoctrineBehaviors\Model\Avatarizable\Avatarizable
	 */
	public function setAvatar(ImageInterface $avatar)
	{
		$this->avatar = $avatar;
		return $this;
	}
	
	/**
	 * @return \Oka\FileBundle\DoctrineBehaviors\Model\Avatarizable\Avatarizable
	 */
	public function removeAvatar()
	{
		$this->avatar = null;
		return $this;
	}
	
	/**
	 * @param integer $width
	 * @param integer $height
	 * @param string $mode
	 * @param integer $quality
	 * @return string
	 */
	public function getAvatarUriFor($width = null, $height = null, $mode = null, $quality = null)
	{
		return $this->avatar !== null ? $this->avatar->getUriFor($width, $height, $mode, $quality) : $this->defaultUri;
	}
	
	/**
	 * @param string $defaultUri
	 * @return \Oka\FileBundle\DoctrineBehaviors\Model\Avatarizable\Avatarizable
	 */
	public function setDefaultUri($defaultUri)
	{
		$this->defaultUri = $defaultUri;
		return $this;
	}
}