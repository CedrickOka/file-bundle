<?php
namespace Oka\FileBundle\Event;

use Oka\FileBundle\Model\FileInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class FileEvent extends Event
{
	/**
	 * @var FileInterface $file
	 */
	protected $file;

	/**
	 * @var Request $request
	 */
	protected $request;
    
    /**
     * @var Response $response
     */
    protected $response;
	
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

    public function setResponse(Response $response) {
        $this->response = $response;
        return $this;
    }
    
    /**
     * @return Response|null
     */
    public function getResponse() {
        return $this->response;
    }
}
