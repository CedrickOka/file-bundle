<?php
namespace Oka\FileBundle\DoctrineBehaviors\ORM\PictureCoverable;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Oka\FileBundle\DoctrineBehaviors\Model\PictureCoverable\PictureCoverable;
use Oka\FileBundle\DoctrineBehaviors\ORM\AbstractListener;

/**
 * 
 * @author cedrick
 * 
 */
class PictureCoverableListener extends AbstractListener
{	
	/**
	 * @var string $imageDefaultClass
	 */
	protected $imageDefaultClass;
	
	public function __construct(array $mappings, $imageDefaultClass)
	{
		$this->mappings = $mappings;
		$this->imageDefaultClass = $imageDefaultClass;
	}
	
	public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
	{
		$classMetadata = $eventArgs->getClassMetadata();
		
		/** @var \ReflectionClass $reflClass */
		if (null === ($reflClass = $classMetadata->reflClass)) {
			return;
		}
		
		if ($this->isEntitySupported($reflClass)) {
			if ($this->getClassAnalyzer()->hasProperty($reflClass, 'pictureCover')) {
				if ($this->getClassAnalyzer()->hasMethod($reflClass, 'getPictureCover')  AND $this->getClassAnalyzer()->hasMethod($reflClass, 'setPictureCover')) {
					$class = $reflClass->getName();
					
					if (isset($this->mappings[$class])) {
						$mapping = $this->mappings[$class];
						$fetchMode = $mapping['propertie']['fecth_mode'];
						$imageClass = isset($mapping['image_class']) ? $mapping['image_class'] : $this->imageDefaultClass;
						
						switch (strtoupper($fetchMode)) {
							case 'EAGER':
								$fetchMode = ClassMetadata::FETCH_EAGER;
								break;
							case 'LAZY':
								$fetchMode = ClassMetadata::FETCH_LAZY;
								break;
							case 'EXTRA_LAZY':
								$fetchMode = ClassMetadata::FETCH_EXTRA_LAZY;
								break;
						}
					} else {
						$imageClass = $this->imageDefaultClass;
						$fetchMode = ClassMetadata::FETCH_EAGER;
					}
					
					$mapOneToOne = [
							'fieldName' 	=> 'pictureCover',
							'targetEntity' 	=> $imageClass,
							'cascade' 		=> ['all'],
							'fetch' 		=> $fetchMode,
							'joinColumns' 	=> [
									['name' => 'picture_cover_id', 'referencedColumnName' => 'id']
							],
					];
					
					$classMetadata->mapOneToOne($mapOneToOne);
				}
			}
		}
	}
	
	public function getSubscribedEvents()
	{
		return [
				Events::loadClassMetadata
		];
	}
	
	/**
	 * Checks whether provided entity is supported.
	 * 
	 * @param \ReflectionClass $reflClass The reflection class
	 * 
	 * @return Boolean
	 */
	protected function isEntitySupported(\ReflectionClass $reflClass)
	{
		return $this->getClassAnalyzer()->hasTrait($reflClass, PictureCoverable::class, $this->isRecursive);
	}
}