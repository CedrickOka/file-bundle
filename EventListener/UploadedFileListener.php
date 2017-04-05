<?php
namespace Oka\FileBundle\EventListener;

use Oka\FileBundle\Event\UploadedFileEvent;
use Oka\FileBundle\OkaFileEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Oka\FileBundle\Utils\FileUtil;

/**
 * 
 * @author cedrick
 * 
 */
class UploadedFileListener implements EventSubscriberInterface
{
	public function onUploadedFileMoved(UploadedFileEvent $event)
	{
		$user = FileUtil::getSystemOwner();
		$fs = new Filesystem();
		
		$realPaths = $event->getEntity()->getRealPaths();
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