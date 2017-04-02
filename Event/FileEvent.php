<?php
namespace Oka\FileBundle\Event;

use Oka\FileBundle\Model\FileInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author cedrick
 * 
 */
class FileEvent extends Event
{
	protected $file;
	protected $request;
	
	/**
	 * @param FileInterface $file
	 * @param Request $request
	 */
	public function __construct(FileInterface $file, Request $request)
	{
		$this->file = $file;
		$this->request = $request;
	}
	
	/**
	 * @return FileInterface
	 */
	public function getFile() {
		return $this->file;
	}
	
	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}
}