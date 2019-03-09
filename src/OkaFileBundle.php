<?php

namespace Oka\FileBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OkaFileBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		parent::build($container);
		
		$this->addRegisterMappingsPass($container);
	}
	
	/**
	 * @param ContainerBuilder $container
	 */
	private function addRegisterMappingsPass(ContainerBuilder $container)
	{
		$mapping = [realpath(__DIR__.'/Resources/config/doctrine-mapping') => 'Oka\FileBundle\Model'];
		
		if (true === class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
			$container->addCompilerPass(DoctrineOrmMappingsPass::createYamlMappingDriver($mapping, array('oka_file.model_manager_name'), 'oka_file.backend_type_orm'));
		}
		
		if (true === class_exists('Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass')) {
			$container->addCompilerPass(DoctrineMongoDBMappingsPass::createYamlMappingDriver($mapping, array('oka_file.model_manager_name'), 'oka_file.backend_type_mongodb'));
		}
	}
}
