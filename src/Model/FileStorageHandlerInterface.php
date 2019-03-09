<?php
namespace Oka\FileBundle\Model;

/**
 *
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
interface FileStorageHandlerInterface
{
	/**
	 * Open file storage
	 * 
	 * @throws \RuntimeException
	 */
	public function open();
	
	/**
	 * Close file storage
	 */
	public function close();
	
	/**
	 * Clear file storage
	 */
	public function clear();
	
	/**
	 * Clear file storage container
	 * 
	 * @param string $containerName
	 * @throws \RuntimeException
	 */
	public function createContainer($containerName, $user = null, $group = null);
		
	/**
	 * Move file in file storage
	 * 
	 * @param FileInterface $file
	 * @return \Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	public function move(FileInterface $file, $andRemove = true);
	
	/**
	 * Remove file from file storage
	 * 
	 * @param FileInterface $file
	 */
	public function remove(FileInterface $file);
	
	/**
	 * Gets the path without filename
	 *
	 * @param FileInterface $file
	 */
	public function getPath(FileInterface $file);
	
	/**
	 * Gets the absolute path of file
	 *
	 * @param FileInterface $file
	 */
	public function getRealPath(FileInterface $file);
	
	/**
	 * Gets the path without filename
	 *
	 * @param FileInterface $file
	 * @param int $width
	 * @param int $height
	 * @param string $mode
	 * @param int $quality
	 */
	public function getThumbnailPath(FileInterface $file, $mode = 'ratio', $quality = 100, $width = null, $height = null);
	
	/**
	 * Gets the absolute path of file thumbnail
	 *
	 * @param FileInterface $file
	 * @param int $width
	 * @param int $height
	 * @param string $mode
	 * @param int $quality
	 */
	public function getThumbnailRealPath(FileInterface $file, $mode = 'ratio', $quality = 100, $width = null, $height = null);
	
	/**
	 * Gets the file uri
	 * 
	 * @param FileInterface $file
	 */
	public function getUri(FileInterface $file);
	
	/**
	 * Gets the file thumbnail uri
	 *
	 * @param FileInterface $file
	 * @param int $width
	 * @param int $height
	 * @param string $mode
	 * @param int $quality
	 */
	public function getThumbnailUri(FileInterface $file, $mode = 'ratio', $quality = 100, $width = null, $height = null);
}
