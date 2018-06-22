<?php
namespace Oka\FileBundle\DoctrineBehaviors\ODM\PictureCoverizable;

use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Oka\FileBundle\DoctrineBehaviors\Model\PictureCoverizable\PictureCoverizable;
use Oka\FileBundle\DoctrineBehaviors\ODM\AbstractListener;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class PictureCoverizableListener extends AbstractListener
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
			if ($this->getClassAnalyzer()->hasProperty($reflClass, 'pictureCover')) {
				if ($this->getClassAnalyzer()->hasMethod($reflClass, 'getPictureCover') && $this->getClassAnalyzer()->hasMethod($reflClass, 'setPictureCover')) {
					$map = $this->handleDocumentMapping($reflClass->getName(), [
							'name' 				=> 'picture_cover',
							'fieldName' 		=> 'pictureCover',
							'targetDocument' 	=> $this->defaultTargetObject
					]);
					
					if (isset($this->mappings[$reflClass->getName()]['embedded']) && true === $this->mappings[$reflClass->getName()]['embedded']) {
						$classMetadata->mapOneEmbedded($map);
					} else {
						$map['storeAs'] = ClassMetadata::REFERENCE_STORE_AS_DB_REF_WITH_DB;
						$map['orphanRemoval'] = true;
						$map['cascade'] = ['all'];
						$classMetadata->mapOneReference($map);
					}
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
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\DoctrineBehaviors\Common\AbstractListener::isEntitySupported()
	 */
	protected function isEntitySupported(\ReflectionClass $reflClass)
	{
		return $this->getClassAnalyzer()->hasTrait($reflClass, 'Oka\FileBundle\DoctrineBehaviors\Model\PictureCoverizable\PictureCoverizable', $this->isRecursive);
	}
}
