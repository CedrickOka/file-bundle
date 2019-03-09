<?php
namespace Oka\FileBundle\Service;

use Oka\FileBundle\Model\FileInterface;
use Oka\FileBundle\Model\FileStorageHandlerInterface;
use Symfony\Component\Finder\Finder;

/**
 *
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class NativeFileStorageHandler implements FileStorageHandlerInterface
{
	/**
	 * @var string $rootPath
	 */
	protected $rootPath;
	
	/**
	 * @var array $webserver
	 */
	protected $webserver;
	
	/**
	 * @var ContainerParameterBag $containerBag
	 */
	protected $containerBag;
	
	/**
	 * @var boolean $open
	 */
	protected $open;
	
	public function __construct($rootPath, array $webserver, ContainerParameterBag $containerBag)
	{
		$this->rootPath = $rootPath;
		$this->webserver = $webserver;
		$this->containerBag = $containerBag;
		$this->open = false;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileStorageHandlerInterface::open()
	 */
	public function open()
	{
		if (!$path = realpath($this->rootPath)) {
			throw new \RuntimeException(sprintf('Unable to open the native file storage handler because the directory "%s" doesn\'t exist', $this->rootPath));
		}
		$this->rootPath = $path;
		
		if (true === file_exists($this->rootPath) && true === is_writable($this->rootPath)) {
			return;
		}
		
		while (false === file_exists($path)) {
			$path = dirname($path);
		}
		
		if (false === is_writable($path)) {
			throw new \RuntimeException(sprintf('Unable to open the native file storage handler because the directory "%s" is not writable', $path));
		}
		
		if (false === mkdir($this->rootPath, 0755, true)) {
			throw new \RuntimeException('Unable to open the native file storage handler.');
		}
		
		$this->open = true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileStorageHandlerInterface::close()
	 */
	public function close()
	{
		$this->open = false;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileStorageHandlerInterface::clear()
	 */
	public function clear()
	{
		if ($handle = opendir($this->rootPath)) {
			while ($entry = readdir($handle)) {
				if ('.' === $entry || '..' === $entry) {
					continue;
				}
				
				$filename = $this->rootPath . '/' . $entry;
				
				if (false === is_dir($filename)) {
					unlink($filename);
				} else {
// 					$this->clear();
					rmdir($filename);
				}
			}
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileStorageHandlerInterface::createContainer()
	 */
	public function createContainer($name, $user = null, $group = null)
	{
		if (false === $this->open) {
			throw new \RuntimeException(sprintf('Unable to create container "%s" because the native file storage handler is not opened.', $name));
		}
		
		$path = sprintf('%s/%s', $this->rootPath, $name);
		
		if (false === mkdir($path, 0755, true)) {
			throw new \RuntimeException(sprintf('Container creation is failed.', $name));
		}
		if (null === $user) {
			chown($path, $user);
		}
		if (null === $group) {
			chgrp($path, $group);
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileStorageHandlerInterface::move()
	 */
	public function move(FileInterface $file, $andRemove = true)
	{
		if (null === ($uploadedFile = $file->getUploadedFile())) {
			return;
		}
		
		$path = $this->getPath($file);
		
		if (false === file_exists($path)) {
			mkdir($path, 0755, true);
		}
		
		if (true === $andRemove) {
			$this->remove($file);
		}
		
		$uploadedFile->move($path, $file->getFilename());
		$file->setUploadedFile(null);
		
		return $uploadedFile;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileStorageHandlerInterface::remove()
	 */
	public function remove(FileInterface $file)
	{
		$finder = new Finder();
		$finder->files()->in($this->getPath($file))->name($file->getFilename());
		
		/** @var \Symfony\Component\Finder\SplFileInfo $finded */
		foreach ($finder as $finded) {
			unlink($finded->getRealPath());
		}
	}
	
	public function getPath(FileInterface $file)
	{
		$path = $this->rootPath;
		
		if ($containerName = $this->getContainerName($file)) {
			$path .= '/' . $containerName;
		}
		
		return $path;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileStorageHandlerInterface::getRealPath()
	 */
	public function getRealPath(FileInterface $file)
	{
		return $this->getPath($file) . '/' . $file->getFilename();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileStorageHandlerInterface::getThumbnailPath()
	 */
	public function getThumbnailPath(FileInterface $file, $mode = 'ratio', $quality = 100, $width = null, $height = null)
	{
		$path = $this->rootPath;
		$size = ($width ?: '') . 'x' . ($height ?: '');
		
		if ($containerName = $this->getContainerName($file)) {
			$path .= '/' . $containerName;
		}
		if (null !== $mode) {
			$path .= '/' . $mode;
		}
		if (null !== $quality) {
			$path .= '/' . $quality;
		}
		
		return sprintf('%s/%s', $path, $size);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileStorageHandlerInterface::getThumbnailRealPath()
	 */
	public function getThumbnailRealPath(FileInterface $file, $mode = 'ratio', $quality = 100, $width = null, $height = null)
	{
		return $this->getThumbnailPath($file, $mode, $quality, $width, $height) . '/' . $file->getFilename();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileStorageHandlerInterface::getUri()
	 */
	public function getUri(FileInterface $file)
	{
		return sprintf('%s/%s?v=%s%s', $this->getBaseUri($file), $file->getFilename(), $file->getVersion(), null !== $this->webserver['query'] ? '&'.$this->webserver['query'] : '');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileStorageHandlerInterface::getThumbnailUri()
	 */
	public function getThumbnailUri(FileInterface $file, $mode = 'ratio', $quality = 100, $width = null, $height = null)
	{
		$baseUri = $this->getBaseUri($file);
		$size = ($width ?: '') . 'x' . ($height ?: '');
		
		if (null !== $mode) {
			$baseUri .= '/' . $mode;
		}
		if (null !== $quality) {
			$baseUri .= '/' . $quality;
		}
		
		return sprintf('%s/%s/%s?v=%s%s', $baseUri, $size, $file->getFilename(), $file->getVersion(), null !== $this->webserver['query'] ? '&'.$this->webserver['query'] : '');
	}
	
	protected function getContainerName(FileInterface $file)
	{
		if (!$container = $this->containerBag->get($file, null)) {
			return null;
		}
		
		return trim($container['name'], '/');
	}
	
	protected function getBaseUri(FileInterface $file)
	{
		$uri = $this->webserver['scheme'] . '://';
		
		if (null !== $this->webserver['user']) {
			$uri .= $this->webserver['user'];
			
			if (null !== $this->webserver['password']) {
				$uri .= ':' . $this->webserver['password'];
			}
			
			$uri .= '@';
		}
		
		$uri .= $this->webserver['host'];
		
		if (null !== $this->webserver['port'] && 80 !== $this->webserver['port']) {
			$uri .= ':' . $this->webserver['port'];
		}
		
		if ('/' !== $this->webserver['path']) {
			$uri .= $this->webserver['path'];
		}
		
		if ($containerName = $this->getContainerName($file)) {
			$uri .= '/' . $containerName;
		}
		
		return $uri;
	}
}
