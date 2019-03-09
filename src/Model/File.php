<?php
namespace Oka\FileBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

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
	 * @var int $size
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
	 * @AssertFile()
	 * @var UploadedFile $uploadedFile
	 */
	protected $uploadedFile;
	
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
	 * @return \Oka\FileBundle\Model\File
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
	 * @return \Oka\FileBundle\Model\File
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
	 * @return \Oka\FileBundle\Model\File
	 */
	public function setExtension($extension)
	{
		$this->extension = $extension;
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getSize()
	{
		return $this->size;
	}
	
	/**
	 * @param int $size
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
	 * @return \Oka\FileBundle\Model\File
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
	 * @param \DateTime $updatedAt
	 * @return \Oka\FileBundle\Model\File
	 */
	public function setUpdatedAt(\DateTime $updatedAt = null)
	{
		$this->updatedAt = $updatedAt ?: new \DateTime();
		return $this;
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
		$now = new \DateTime();
		
		if (null === $this->createdAt) {
			$this->createdAt = $now;
		}
		
		$this->updatedAt = $now;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileInterface::hasUploadedFile()
	 */
	public function hasUploadedFile()
	{
		return $this->uploadedFile instanceof UploadedFile;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileInterface::getUploadedFile()
	 */
	public function getUploadedFile()
	{
		return $this->uploadedFile;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileInterface::setUploadedFile()
	 */
	public function setUploadedFile(UploadedFile $uploadedFile = null)
	{
		$this->uploadedFile = $uploadedFile;
		
		if (null !== $this->uploadedFile) {
			if (null === ($this->extension = $this->uploadedFile->guessExtension())) {
				$this->extension = $this->uploadedFile->getExtension();
			}
			
			if (null === $this->name) {
				$this->name = $this->uploadedFile->getClientOriginalName() ?: $this->uploadedFile->getFilename();
			}
			
			$this->mimeType = $this->uploadedFile->getMimeType();
			$this->size = $this->uploadedFile->getSize();
		}
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileInterface::getFilename()
	 */
	public function getFilename()
	{
		return sprintf('%s.%s', $this->id, $this->extension ? '.' . $this->extension : '');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\Model\FileInterface::getVersion()
	 */
	public function getVersion()
	{
		return $this->updatedAt instanceof \DateTime ? $this->updatedAt->getTimestamp() : 0;
	}
	
	public static function createFromUploadedFile(UploadedFile $file)
	{
		$image = new self();
		$image->setUploadedFile($file);
		
		return $image;
	}
	
	public static function createFromPath($path, $originalName)
	{
		$file = new UploadedFile($path, $originalName, finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path), filesize($path), UPLOAD_ERR_OK, true);
		
		return self::createFromUploadedFile($file);
	}
}
