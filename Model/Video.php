<?php
namespace Oka\FileBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 * @ORM\MappedSuperclass()
 */
abstract class Video extends File
{
	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $duration;
	
	/**
	 * @return the \DateTime
	 */
	public function getDuration()
	{
		return $this->duration;
	}
	
	/**
	 * @param \DateTime $duration
	 */
	public function setDuration($duration)
	{
		$this->duration = $duration;
		return $this;
	}
}