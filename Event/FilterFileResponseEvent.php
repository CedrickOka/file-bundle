<?php
namespace Oka\FileBundle\Event;

use Oka\FileBundle\Model\FileInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * @author cedrick
 * 
 */
class FilterFileResponseEvent extends FileEvent
{
	protected $response;
	
	/**
	 * @param FileInterface $file
	 * @param Request $request
	 * @param Response $response
	 */
	public function __construct(FileInterface $file, Request $request, Response $response)
	{
		parent::__construct($file, $request);
		
		$this->response = $response;
	}
	
	/**
	 * @return Response
	 */
	public function getResponse() {
		return $this->response;
	}
}