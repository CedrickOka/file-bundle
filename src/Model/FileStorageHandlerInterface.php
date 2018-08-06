<?php
namespace Oka\FileBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 *
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
interface FileStorageHandlerInterface
{
	/**
	 * @param FileInterface $file
	 * @throws \Exception if access is not available
	 */
	public function checkAccess(FileInterface $file);
	
	/**
	 * Save file in the storage
	 * 
	 * @param FileInterface $file
	 * @return UploadedFile
	 */
	public function save(FileInterface $file);
	
	/**
	 * Remove file from the storage
	 * 
	 * @param FileInterface $file
	 */
	public function remove(FileInterface $file);
}
