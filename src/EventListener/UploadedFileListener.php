<?php
namespace Oka\FileBundle\EventListener;

use Oka\FileBundle\OkaFileEvents;
use Oka\FileBundle\Event\UploadedFileEvent;
use Oka\FileBundle\Utils\FileUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class UploadedFileListener implements EventSubscriberInterface
{
	/** 
	 * @var string $owner
	 */
	protected $owner;
	
	/**
	 * @var string $group
	 */
	protected $group;
	
	public function __construct($owner, $group)
	{
		$this->owner = $owner;
		$this->group = $group;
	}
	
	public function onUploadedFileMoved(UploadedFileEvent $event)
	{
		$user = FileUtil::getSystemOwner();
		$realPaths = $event->getObject()->getRealPaths();
		
		FileUtil::getFs()->chown($realPaths, $user);
		FileUtil::getFs()->chgrp($realPaths, $user);
	}
	
	public static function getSubscribedEvents()
	{
		return [
				OkaFileEvents::UPLOADED_FILE_MOVED => ['onUploadedFileMoved', -255]
		];
	}
}
