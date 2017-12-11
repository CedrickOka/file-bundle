<?php
namespace Oka\FileBundle\DoctrineBehaviors\ODM\Avatarizable;

use Oka\FileBundle\DoctrineBehaviors\Model\Avatarizable\Avatarizable;
use Oka\FileBundle\DoctrineBehaviors\ODM\AbstractListener;
use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class AvatarizableListener extends AbstractListener
{
	public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
	{
		/** @var \Doctrine\ODM\MongoDB\Mapping\ClassMetadata $classMetadata */
		$classMetadata = $eventArgs->getClassMetadata();
		
		/** @var \ReflectionClass $reflClass */
		if (null === ($reflClass = $classMetadata->reflClass)) {
			return;
		}
		
		if ($this->isEntitySupported($reflClass)) {
			if ($this->getClassAnalyzer()->hasProperty($reflClass, 'avatar')) {
				if ($this->getClassAnalyzer()->hasMethod($reflClass, 'getAvatar') && $this->getClassAnalyzer()->hasMethod($reflClass, 'setAvatar')) {
					$map = $this->handleEntityMapping($reflClass->getName(), [
							'fieldName' 		=> 'avatar',
							'targetDocument' 	=> $this->defaultTargetObject,
							'storeAs' 			=> ClassMetadata::REFERENCE_STORE_AS_DB_REF_WITH_DB,
							'cascade' 			=> ['all'],
							'orphanRemoval' 	=> true
					]);
					
					if (isset($this->mappings[$reflClass->getName()]['embedded']) && true === $this->mappings[$reflClass->getName()]['embedded']) {
						$classMetadata->mapOneEmbedded($map);
					} else {
						$classMetadata->mapOneReference($map);
					}
				}
			}
		}
	}
	
	public function prePersist(LifecycleEventArgs $arg)
	{
		$document = $arg->getDocument();
		$reflClass = new \ReflectionClass($document);
		
		if ($this->isEntitySupported($reflClass)) {
			if ($this->getClassAnalyzer()->hasProperty($reflClass, 'defaultUri') && $this->getClassAnalyzer()->hasMethod($reflClass, 'setDefaultUri')) {
				$document->setDefaultUri(isset($this->mappings[$reflClass->getName()]['options']['default_avatar_uri']) ? $this->mappings[$reflClass->getName()]['options']['default_avatar_uri'] : '');
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
	 * @see \Oka\FileBundle\DoctrineBehaviors\AbstractListener::isEntitySupported()
	 */
	protected function isEntitySupported(\ReflectionClass $reflClass)
	{
		return $this->getClassAnalyzer()->hasTrait($reflClass, Avatarizable::class, $this->isRecursive);
	}
}
