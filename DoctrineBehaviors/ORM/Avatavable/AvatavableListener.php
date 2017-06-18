<?php
namespace Oka\FileBundle\DoctrineBehaviors\ORM\Avatavable;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Oka\FileBundle\DoctrineBehaviors\Model\Avatavable\Avatavable;
use Oka\FileBundle\DoctrineBehaviors\ORM\AbstractListener;

/**
 *
 * @author cedrick
 *        
 */
class AvatavableListener extends AbstractListener
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
			if ($this->getClassAnalyzer()->hasProperty($reflClass, 'avatar')) {
				if ($this->getClassAnalyzer()->hasMethod($reflClass, 'getAvatar')  AND $this->getClassAnalyzer()->hasMethod($reflClass, 'setAvatar')) {
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
							'fieldName' 	=> 'avatar',
							'targetEntity' 	=> $imageClass,
							'cascade' 		=> ['persist'],
							'fetch' 		=> $fetchMode,
							'joinColumns' 	=> [
									['name' => 'avatar_id', 'referencedColumnName' => 'id']
							],
					];
					
					$classMetadata->mapOneToOne($mapOneToOne);
				}
			}
		}
	}
	
	public function prePersist(LifecycleEventArgs $arg)
	{
		$entity = $arg->getEntity();
		$reflClass = new \ReflectionClass($entity);
		
		if ($this->isEntitySupported($reflClass)) {
			if ($this->getClassAnalyzer()->hasProperty($reflClass, 'domain') AND $this->getClassAnalyzer()->hasProperty($reflClass, 'uploadDir') AND $this->getClassAnalyzer()->hasProperty($reflClass, 'uploadRootDir')) {
				$entity->domain = $this->domain;
				$entity->uploadDir = $this->uploadDir;
				$entity->uploadRootDir = $this->uploadRootDir;
			}
		}
	}
	
	public function postLoad(LifecycleEventArgs $arg)
	{
		$this->prePersist($arg);
	}
	
	public function getSubscribedEvents()
	{
		return [
			Events::loadClassMetadata,
			Events::prePersist,
			Events::postLoad
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
		return $this->getClassAnalyzer()->hasTrait($reflClass, Avatavable::class, $this->isRecursive);
	}
}