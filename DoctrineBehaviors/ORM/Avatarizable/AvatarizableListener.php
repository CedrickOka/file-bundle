<?php
namespace Oka\FileBundle\DoctrineBehaviors\ORM\Avatarizable;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Oka\FileBundle\DoctrineBehaviors\Model\Avatarizable\Avatarizable;
use Oka\FileBundle\DoctrineBehaviors\ORM\AbstractListener;

/**
 * 
 * @author cedrick
 * 
 */
class AvatarizableListener extends AbstractListener
{
	public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
	{
		$classMetadata = $eventArgs->getClassMetadata();
		
		/** @var \ReflectionClass $reflClass */
		if (null === ($reflClass = $classMetadata->reflClass)) {
			return;
		}
		
		if ($this->isEntitySupported($reflClass)) {
			if ($this->getClassAnalyzer()->hasProperty($reflClass, 'avatar')) {
				if ($this->getClassAnalyzer()->hasMethod($reflClass, 'getAvatar') && $this->getClassAnalyzer()->hasMethod($reflClass, 'setAvatar')) {
					$mapOneToOne = $this->handleEntityMapping($reflClass->getName(), [
							'fieldName' 	=> 'avatar',
							'targetEntity' 	=> $this->defaultTargetEntity,
							'cascade' 		=> ['all'],
							'fetch' 		=> ClassMetadata::FETCH_EAGER,
							'joinColumns' 	=> [
									['name' => 'avatar_id', 'referencedColumnName' => 'id']
							],
					]);
					
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
			if ($this->getClassAnalyzer()->hasProperty($reflClass, 'defaultUri') && $this->getClassAnalyzer()->hasMethod($reflClass, 'setDefaultUri')) {
				$entity->setDefaultUri(isset($this->mappings[$reflClass->getName()]['options']['default_avatar_uri']) ? $this->mappings[$reflClass->getName()]['options']['default_avatar_uri'] : '');
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
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\DoctrineBehaviors\ORM\AbstractListener::isEntitySupported()
	 */
	protected function isEntitySupported(\ReflectionClass $reflClass)
	{
		return $this->getClassAnalyzer()->hasTrait($reflClass, Avatarizable::class, $this->isRecursive);
	}
}