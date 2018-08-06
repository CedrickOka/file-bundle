<?php
namespace Oka\FileBundle\Service;

use Oka\FileBundle\Model\FileInterface;
use Oka\FileBundle\Model\FileStorageHandlerInterface;
use Oka\FileBundle\Utils\FileUtil;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 *
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class NativeFileStorageHandler implements FileStorageHandlerInterface
{
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileStorageHandlerInterface::checkAccess()
	 */
	public function checkAccess(FileInterface $file) {
		$path = FileUtil::findParentDirectoyThatExists($file->getPath());
		
		if (false === is_writable($path)) {
			throw new FileException(sprintf('Unable to write in the "%s" directory', $path));
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileStorageHandlerInterface::save()
	 */
	public function save(FileInterface $file) {
		if (null === ($uploadedFile = $file->getUploadedFile())) {
			return;
		}
		
		if (false === $file->exists($file->getPath())) {
			$file->mkdir($file->getPath());
		}
		
		if (null !== $file->getLastModified()) {
			$this->remove($file);
		}
		
		$uploadedFile->move($file->getPath(), $file->getFilename());
		$file->setUploadedFile(null);
		
		return $uploadedFile;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileStorageHandlerInterface::remove()
	 */
	public function remove(FileInterface $file) {
		$file->removeFile();
	}
}
