<?php
namespace Oka\FileBundle\DoctrineBehaviors\ODM;

use Oka\FileBundle\DoctrineBehaviors\Common\AbstractListener as BaseAbstractListener;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
abstract class AbstractListener extends BaseAbstractListener
{
	/**
	 * @param string $class
	 * @param array $mapping
	 * @return array
	 */
	protected function handleEntityMapping($class, $mapping)
	{
		if (isset($this->mappings[$class])) {
			$mapping['targetDocument'] = isset($this->mappings[$class]['target_object']) ? $this->mappings[$class]['target_object'] :
			(isset($this->mappings[$class]['target_entity']) ? $this->mappings[$class]['target_entity'] : $this->defaultTargetObject);
		}
		
		return $mapping;
	}
}
