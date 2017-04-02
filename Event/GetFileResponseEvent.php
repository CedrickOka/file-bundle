<?php
namespace Oka\FileBundle\Event;

use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * @author cedrick
 * 
 */
class GetFileResponseEvent extends FileEvent
{
	protected $response;

	public function setResponse(Response $response) {
		$this->response = $response;
	}

	/**
	 * @return Response|null
	 */
	public function getResponse() {
		return $this->response;
	}
}
