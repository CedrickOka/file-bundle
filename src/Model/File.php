<?php
namespace Oka\FileBundle\Model;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
abstract class File implements FileInterface
{
	/**
	 * @var mixed $id
	 */
	protected $id;
	
	/**
	 * @var string $name
	 */
	protected $name;
	
	/**
	 * @var string $mimeType
	 */
	protected $mimeType;
	
	/**
	 * @var string $extension
	 */
	protected $extension;
	
	/**
	 * @var integer $size
	 */
	protected $size;
	
	/**
	 * @var \DateTime $createdAt
	 */
	protected $createdAt;
	
	/**
	 * @var \DateTime $updatedAt
	 */
	protected $updatedAt;
	
	/**
	 * @var string $rootPath
	 */
	protected $rootPath;
	
	/**
	 * @var string $dirname
	 */
	protected $dirname;
	
	/**
	 * @var string $host
	 */
	protected $host;
	
	/**
	 * @var integer $port
	 */
	protected $port;
	
	/**
	 * @var boolean $secure
	 */
	protected $secure;
	
	/**
	 * @Assert\File()
	 * @var UploadedFile $uploadedFile
	 */
	protected $uploadedFile;
	
	/**
	 * @var Filesystem $fs
	 */
	protected $fs;
	
	/**
	 * @var string $systemOwner
	 */
	protected $systemOwner;
	
	/**
	 * @var mixed $tmp
	 */
	private $tmp;
	
	public function __construct()
	{
		$this->size = 0;
	}
	
	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @param mixed $id
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getMimeType()
	{
		return $this->mimeType;
	}
	
	/**
	 * @param string $mimeType
	 */
	public function setMimeType($mimeType)
	{
		$this->mimeType = $mimeType;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getExtension()
	{
		return $this->extension;
	}
	
	/**
	 * @param string $extension
	 */
	public function setExtension($extension)
	{
		$this->extension = $extension;
		return $this;
	}
	
	/**
	 * @return integer
	 */
	public function getSize()
	{
		return $this->size;
	}
	
	/**
	 * @param integer $size
	 */
	public function setSize($size)
	{
		$this->size = $size;
		return $this;
	}
	
	/**
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}
	
	/**
	 * @param \DateTime $createdAt
	 */
	public function setCreatedAt(\DateTime $createdAt = null)
	{
		$this->createdAt = $createdAt ?: new \DateTime();
		return $this;
	}
	
	/**
	 * @return \DateTime
	 */
	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}
	
	/**
	 * @param \DateTime $createdAt
	 */
	public function setUpdatedAt(\DateTime $updatedAt = null)
	{
		$this->updatedAt = $updatedAt ?: new \DateTime();
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getFilename()
	{
		return $this->id . ($this->extension ? '.' . $this->extension : '');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileInterface::getPath()
	 */
	public function getPath()
	{
		return $this->rootPath . ($this->dirname ? '/' . $this->dirname : '');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileInterface::getRealPath()
	 */
	public function getRealPath()
	{
		return $this->getPath() . '/' . $this->getFilename();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileInterface::getLastModified()
	 */
	public function getLastModified()
	{
		return $this->updatedAt instanceof \DateTime ? $this->updatedAt : $this->createdAt;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileInterface::setLastModified()
	 */
	public function setLastModified() 
	{
		if (null === $this->createdAt) {
			$this->setCreatedAt();
		}
		
		return $this->setUpdatedAt();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileInterface::getVersion()
	 */
	public function getVersion()
	{
		return $this->updatedAt instanceof \DateTime ? $this->updatedAt->getTimestamp() : null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileInterface::getUri()
	 */
	public function getUri()
	{
		return sprintf(
				'%s://%s/%s%s',
				$this->secure === true ? 'https' : 'http',
				$this->host . ($this->port === null ? '' : ':' . $this->port),
				$this->dirname ? $this->dirname . '/' . $this->getFilename() : $this->getFilename(),
				$this->createQueryStringForURI()
		);
	}
	
	/**
	 * @param string $rootPath
	 */
	public function setRootPath($rootPath)
	{
		$this->rootPath = $rootPath;
		return $this;
	}
	
	/**
	 * @param string $dirname
	 */
	public function setDirname($dirname)
	{
		$this->dirname = $dirname;
		return $this;
	}
	
	/**
	 * @param string $host
	 */
	public function setHost($host)
	{
		$this->host = $host;
		return $this;
	}
	
	/**
	 * @param integer $port
	 */
	public function setPort($port)
	{
		$this->port = $port;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function isSecure()
	{
		return $this->secure;
	}
	
	/**
	 * @param boolean $secure
	 */
	public function setSecure($secure)
	{
		$this->secure = $secure;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function hasUploadedFile()
	{
		return $this->uploadedFile !== null;
	}
	
	/**
	 * @return UploadedFile
	 */
	public function getUploadedFile()
	{
		return $this->uploadedFile;
	}
	
	/**
	 * @param UploadedFile $uploadedFile
	 */
	public function setUploadedFile(UploadedFile $uploadedFile = null)
	{
		$this->uploadedFile = $uploadedFile;
		
		if ($this->uploadedFile !== null) {
			$this->prepareMoveFile();
			
			if ($this->createdAt !== null) {
				$this->prepareDeletionFileInContainer();
			}
		}
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getRealPaths()
	{
		$files = [];
		$finder = new Finder();
		$finder->files()->in($this->getPath())->name($this->getFileName());
		
		/** @var SplFileInfo $file */
		foreach ($finder as $file) {
			$files[] = $file->getRealPath();
		}
		
		return $files;
	}
	
	public function moveFile()
	{
		if ($this->uploadedFile === null) {
			return;
		}
		
		if (!$this->fs->exists($this->getPath())) {
			$this->mkdir($this->getPath());
		}

		$uploadedFile = $this->uploadedFile;
		$this->deleteFileInContainer();
		
		$this->uploadedFile->move($this->getPath(), $this->getFilename());
		$this->setUploadedFile(null);
		
		return $uploadedFile;
	}
	
	public function removeFile()
	{
		$this->prepareDeletionFileInContainer();
		$this->deleteFileInContainer();
	}
	
	protected function createQueryStringForURI(array $params = [])
	{
		$query = '';
		
		if ($this->updatedAt instanceof \DateTime) {
			$params = array_merge(['v' => $this->updatedAt->getTimestamp()], $params);
		}
		
		if (!empty($params)) {
			foreach ($params as $key => $value) {
				$params[$key] = $key . '=' . $value;
			}
			$query = '?' . implode('&', $params);
		}
		
		return $query;	
	}
	
	public function mkdir($dirs, $mode = 0755, $owner = null, $group = null, $recursive = true)
	{
		$this->fs->mkdir($dirs, $mode);
		$this->fs->chown($dirs, $owner ?: $this->systemOwner, $recursive);
		$this->fs->chgrp($dirs, $group ?: $this->systemOwner, $recursive);
	}
	
	protected function prepareDeletionFileInContainer()
	{
		$this->tmp = $this->getRealPaths();
	}
	
	protected function deleteFileInContainer()
	{		
		if (is_array($this->tmp) && !empty($this->tmp)) {
			$this->fs->remove($this->tmp);
		}
	}
	
	protected function prepareMoveFile()
	{
		if (null === ($this->extension = $this->uploadedFile->guessExtension())) {
			$this->extension = $this->uploadedFile->getExtension() ?: '';
		}
		
		if ($this->name === null) {
			$this->name = $this->uploadedFile->getClientOriginalName() ?: $this->uploadedFile->getFilename();
		}
		
		$this->mimeType = $this->uploadedFile->getMimeType();
		$this->size = $this->uploadedFile->getSize();
	}
	
	public function setFileSystem($fs)
	{
		$this->fs = $fs;
	}
	
	public function setSystemOwner($systemOwner)
	{
		$this->systemOwner = $systemOwner;
	}
}
