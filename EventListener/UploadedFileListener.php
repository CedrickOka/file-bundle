<?php
namespace Oka\FileBundle\EventListener;

use Oka\FileBundle\Event\UploadedFileEvent;
use Oka\FileBundle\OkaFileEvents;
use Oka\FileBundle\Utils\FileUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;

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
		
	}
	
	public function onUploadedFileMoved(UploadedFileEvent $event)
	{
		$user = FileUtil::getSystemOwner();
		$fs = new Filesystem();
		
		$realPaths = $event->getObject()->getRealPaths();
		$fs->chown($realPaths, $user);
		$fs->chgrp($realPaths, $user);		
	}
	
	public static function getSubscribedEvents()
	{
		return [
				OkaFileEvents::UPLOADED_FILE_MOVED => ['onUploadedFileMoved', -255]
		];
	}
}
