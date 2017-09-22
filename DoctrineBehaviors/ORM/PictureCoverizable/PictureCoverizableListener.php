<?php
namespace Oka\FileBundle\DoctrineBehaviors\ORM\PictureCoverizable;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Oka\FileBundle\DoctrineBehaviors\Model\PictureCoverable\PictureCoverable;
use Oka\FileBundle\DoctrineBehaviors\Model\PictureCoverizable\PictureCoverizable;
use Oka\FileBundle\DoctrineBehaviors\ORM\AbstractListener;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class PictureCoverizableListener extends AbstractListener
{	
	public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
	{
		/** @var \Doctrine\ORM\Mapping\ClassMetadata $classMetadata */
		$classMetadata = $eventArgs->getClassMetadata();
		
		/** @var \ReflectionClass $reflClass */
		if (null === ($reflClass = $classMetadata->reflClass)) {
			return;
		}
		
		if ($this->isEntitySupported($reflClass)) {
			if ($this->getClassAnalyzer()->hasProperty($reflClass, 'pictureCover')) {
				if ($this->getClassAnalyzer()->hasMethod($reflClass, 'getPictureCover') && $this->getClassAnalyzer()->hasMethod($reflClass, 'setPictureCover')) {
					$mapOneToOne = $this->handleEntityMapping($reflClass->getName(), [
							'fieldName' 	=> 'pictureCover',
							'targetEntity' 	=> $this->defaultTargetEntity,
							'cascade' 		=> ['all'],
							'fetch' 		=> ClassMetadata::FETCH_EAGER,
							'joinColumns' 	=> [
									['name' => 'picture_cover_id', 'referencedColumnName' => 'id']
							],
					]);
					
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
	 * {@inheritDoc}
	 * @see \Oka\FileBundle\DoctrineBehaviors\ORM\AbstractListener::isEntitySupported()
	 */
	protected function isEntitySupported(\ReflectionClass $reflClass)
	{
		return $this->getClassAnalyzer()->hasTrait($reflClass, PictureCoverizable::class, $this->isRecursive) || 
				$this->getClassAnalyzer()->hasTrait($reflClass, PictureCoverable::class, $this->isRecursive);
	}
}
