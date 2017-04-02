<?php
namespace Oka\FileBundle\Event;

use Oka\FileBundle\Model\FileInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * 
 * @author cedrick
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
	
	public function getEntity()
	{
		return $this->entity;
	}
	
	public function getUploadedFile()
	{
		return $this->uploadedFile;
	}
}