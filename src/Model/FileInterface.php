<?php
namespace Oka\FileBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 *
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
interface FileInterface
{
	/**
	 * Get the file name without path
	 *
	 * @return string
	 */
	public function getFilename();
	
	/**
	 * Get the file mime-type
	 *
	 * @return string
	 */
	public function getMimeType();
	
	/**
	 * Get the file size in octets
	 *
	 * @return integer
	 */
	public function getSize();
	
	/**
	 * Get the last modified date of file
	 *
	 * @return \DateTime|NULL
	 */
	public function getLastModified();
	
	/**
	 * Set the last modified date of file
	 */
	public function setLastModified();
	
	/**
	 * Get the version of the file according to the update date
	 * @return integer
	 */
	public function getVersion();
	
	/**
	 * Check if has upload file
	 *
	 * @return boolean
	 */
	public function hasUploadedFile();
	
	/**
	 * @return UploadedFile
	 */
	public function getUploadedFile();
	
	/**
	 * @param UploadedFile $uploadedFile
	 */
	public function setUploadedFile(UploadedFile $uploadedFile = null);
}
