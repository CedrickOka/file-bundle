<?php
namespace Oka\FileBundle;

/**
 * Contains all events thrown in the OkaFileBundle
 * 
 * @author cedrick
 */
final class OkaFileEvents
{
	/**
	 * the UPLOADED_FILE_MOVING event occurs when before a file has been downloaded in container.
	 * 
	 * The event listener method receives a Oka\FileBundle\Event\UploadedFileEvent instance.
	 */
	const UPLOADED_FILE_MOVING = 'oka_file.uploaded_file.moving';
	
	/**
	 * the UPLOADED_FILE_MOVED event occurs when after a file has been downloaded in container.
	 *
	 * The event listener method receives a Oka\FileBundle\Event\UploadedFileEvent instance.
	 */
	const UPLOADED_FILE_MOVED = 'oka_file.uploaded_file.moved';
}