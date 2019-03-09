<?php
namespace Oka\FileBundle\DoctrineBehaviors\Model\Avatarizable;

use Oka\FileBundle\Model\ImageInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
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
		return null !== $this->avatar;
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
	 * @param string $defaultUri
	 * @return \Oka\FileBundle\DoctrineBehaviors\Model\Avatarizable\Avatarizable
	 */
	public function setDefaultUri($defaultUri)
	{
		$this->defaultUri = $defaultUri;
		return $this;
	}
}
