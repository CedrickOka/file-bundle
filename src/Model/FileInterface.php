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
	 * Get the absolute path of the file without the name
	 * 
	 * @return string
	 */
	public function getPath();
	
	/**
	 * Get the absolute path of the file
	 * 
	 * @return string
	 */
	public function getRealPath();
	
	/**
	 * Get the absolutes path of the file
	 * 
	 * @return array
	 */
	public function getRealPaths();
	
	/**
	 * Get the file URI
	 * 
	 * @return string
	 */
	public function getUri();
	
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
	 * @param string $rootPath
	 */
	public function setRootPath($rootPath);
	
	/**
	 * @param string $dirname
	 */
	public function setDirname($dirname);
	
	/**
	 * @param string $host
	 */
	public function setHost($host);
	
	/**
	 * @param integer $port
	 */
	public function setPort($port);
	
	/**
	 * @param boolean $secure
	 */
	public function setSecure($secure);
	
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
	
	/**
	 * Check if path exits in storage
	 * 
	 * @param string $path
	 * @return boolean
	 */
	public function exists($path = null);
	
	/**
	 * Create directory
	 * 
	 * @param string $dirs
	 * @param number $mode
	 * @param string $owner
	 * @param string $group
	 * @param boolean $recursive
	 */
	public function mkdir($dirs, $mode = 0755, $owner = null, $group = null, $recursive = true);
	
	/**
	 * Move file uploaded in final target
	 * 
	 * @return UploadedFile
	 */
	public function moveFile();
	
	/**
	 * Remove all file versions
	 */
	public function removeFile();
}
