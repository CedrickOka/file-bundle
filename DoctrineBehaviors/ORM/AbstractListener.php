<?php
namespace Oka\FileBundle\DoctrineBehaviors\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Mapping\ClassMetadata;
use Oka\FileBundle\DoctrineBehaviors\Reflection\ClassAnalyzer;

/**
 * 
 * @author cedrick
 * 
 */
abstract class AbstractListener implements EventSubscriber
{
	/**
	 * @var ClassAnalyzer $classAnalyser
	 */
	private $classAnalyser;
	
	/**
	 * @var boolean $isRecursive
	 */
	protected $isRecursive;
	
	/**
	 * @var array $mappings
	 */
	protected $mappings;
	
	/**
	 * @var string $defaultTargetEntity
	 */
	protected $defaultTargetEntity;
	
	/**
	 * @param array $mappings
	 * @param string $defaultClass
	 */
	public function __construct(array $mappings, $defaultClass)
	{
		$this->mappings = $mappings;
		$this->defaultTargetEntity = $defaultClass;
	}
	
	/**
	 * @param ClassAnalyzer $classAnalyser
	 */
	public function setClassAnalyzer(ClassAnalyzer $classAnalyser)
	{
		$this->classAnalyser = $classAnalyser;
	}
	
	/**
	 * @param boolean $recursive
	 */
	public function setRecursive($recursive)
	{
		$this->isRecursive = $recursive;
	}
	
	/**
	 * @return \Oka\FileBundle\DoctrineBehaviors\Reflection\ClassAnalyzer
	 */
	protected function getClassAnalyzer()
	{
		return $this->classAnalyser;
	}
	
	/**
	 * @param string $class
	 * @param array $mapping
	 * @return array
	 */
	protected function handleEntityMapping($class, $mapping)
	{
		if (isset($this->mappings[$class])) {
			$mapping['targetEntity'] = isset($this->mappings[$class]['target_entity']) ? 
										$this->mappings[$class]['target_entity'] : $this->defaultTargetEntity;
			
			switch (strtoupper($this->mappings[$class]['fetch'])) {
				case 'EAGER':
					$mapping['fetch'] = ClassMetadata::FETCH_EAGER;
					break;
				case 'LAZY':
					$mapping['fetch'] = ClassMetadata::FETCH_LAZY;
					break;
				case 'EXTRA_LAZY':
					$mapping['fetch'] = ClassMetadata::FETCH_EXTRA_LAZY;
					break;
			}
		}
		
		return $mapping;
	}
	
	/**
	 * Checks whether provided entity is supported.
	 *
	 * @param \ReflectionClass $reflClass The reflection class
	 *
	 * @return Boolean
	 */
	protected abstract function isEntitySupported(\ReflectionClass $reflClass);
}