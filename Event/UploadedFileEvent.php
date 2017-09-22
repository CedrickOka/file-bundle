<?php
namespace Oka\FileBundle\Event;

use Oka\FileBundle\Model\FileInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class UploadedFileEvent extends Event
{
	/**
	 * @var FileInterface $entity
	 */
	protected $entity;
	
	/**
	 * @var UploadedFile $uploadedFile
	 */
	protected $uploadedFile;
	
	public function __construct(FileInterface $entity, UploadedFile $uploadedFile)
	{
		$this->entity = $entity;
		$this->uploadedFile = $uploadedFile;
	}
	
	/**
	 * @return \Oka\FileBundle\Model\FileInterface
	 */
	public function getEntity()
	{
		return $this->entity;
	}
	
	/**
	 * @return \Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	public function getUploadedFile()
	{
		return $this->uploadedFile;
	}
}
