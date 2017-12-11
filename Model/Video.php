<?php
namespace Oka\FileBundle\Model;

/**
 *
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
abstract class Video extends File
{
	/**
	 * Video duration in seconds
	 * @var int $duration
	 */
	protected $duration;
	
	/**
	 * @return int
	 */
	public function getDuration()
	{
		return $this->duration;
	}
	
	/**
	 * @param int $duration
	 */
	public function setDuration($duration)
	{
		$this->duration = $duration;
		return $this;
	}
	
	public function __construct()
	{
		parent::__construct();
		
		$this->duration = 0;
	}
}
