<?php
namespace Oka\FileBundle\DoctrineBehaviors\Common;

use Doctrine\Common\EventSubscriber;
use Oka\FileBundle\DoctrineBehaviors\Reflection\ClassAnalyzer;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
abstract class AbstractListener implements EventSubscriber
{
	/**
	 * @var ClassAnalyzer $classAnalyser
	 */
	protected $classAnalyser;
	
	/**
	 * @var boolean $isRecursive
	 */
	protected $isRecursive;
	
	/**
	 * @var array $mappings
	 */
	protected $mappings;
	
	/**
	 * @var string $defaultTargetObject
	 */
	protected $defaultTargetObject;
	
	/**
	 * @param array $mappings
	 * @param string $defaultClass
	 */
	public function __construct(array $mappings, $defaultClass)
	{
		$this->mappings = $mappings;
		$this->defaultTargetObject = $defaultClass;
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
	 * Checks whether provided entity is supported.
	 *
	 * @param \ReflectionClass $reflClass The reflection class
	 *
	 * @return Boolean
	 */
	protected abstract function isEntitySupported(\ReflectionClass $reflClass);
}
