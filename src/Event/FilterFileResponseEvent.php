<?php
namespace Oka\FileBundle\Event;

use Oka\FileBundle\Model\FileInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class FilterFileResponseEvent extends FileEvent
{
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
}
