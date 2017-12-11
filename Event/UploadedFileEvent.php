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
	 * @var FileInterface $object
	 */
	protected $object;
	
	/**
	 * @var UploadedFile $uploadedFile
	 */
	protected $uploadedFile;
	
	public function __construct(FileInterface $object, UploadedFile $uploadedFile)
	{
		$this->object = $object;
		$this->uploadedFile = $uploadedFile;
	}
	
	/**
	 * @return \Oka\FileBundle\Model\FileInterface
	 */
	public function getObject()
	{
		return $this->object;
	}
	
	/**
	 * @deprecated Use instead UploadedFileEvent::getObject().
	 * @return \Oka\FileBundle\Model\FileInterface
	 */
	public function getEntity()
	{
		return $this->getObject();
	}
	
	/**
	 * @return \Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	public function getUploadedFile()
	{
		return $this->uploadedFile;
	}
}
