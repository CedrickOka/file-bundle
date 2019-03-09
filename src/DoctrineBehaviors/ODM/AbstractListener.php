<?php
namespace Oka\FileBundle\DoctrineBehaviors\ODM;

use Oka\FileBundle\DoctrineBehaviors\Common\AbstractListener as BaseAbstractListener;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

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
	protected function handleDocumentMapping($class, $mapping)
	{
		if (false === isset($this->mappings[$class])) {
			throw new InvalidConfigurationException(sprintf('No mapping is defined for the "%s" class to use the behavior.', $class));
		}
		
		$mapping['targetDocument'] = $this->mappings[$class]['target_object'];
		
		return $mapping;
	}
}
