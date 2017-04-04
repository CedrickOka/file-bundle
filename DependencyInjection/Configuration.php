<?php
namespace Oka\FileBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('oka_file');

		$rootNode
			->addDefaultsIfNotSet()
			->children()
				->scalarNode('model_manager_name')->defaultNull()->end()
				->append($this->objectClassNodeDefinition())
				->append($this->containerConfigNodeDefinition())
				->append($this->imageNodeDefinition())
				->append($this->behaviorsNodeDefinition())				
				->arrayNode('routing')
					->useAttributeAsKey('name')
					->prototype('array')
						->addDefaultsIfNotSet()
						->children()
							->scalarNode('file_class')->isRequired()->end()
							->scalarNode('host')->defaultNull()->end()
							->scalarNode('scheme')->defaultNull()->end()
							->scalarNode('prefix')
								->defaultNull()
								->treatNullLike('/')
								->validate()
								->ifTrue(function($value){
									if ($value) {
										if ($value == '/') {
											return false;
										}
										
										$i = strlen($value) - 1;
										return $value[0] !== '/' || $value[$i] === '/';
									}
								})
								->thenInvalid('Invalid prefix "%s". The prefix must start with "/" and can not end with "/".')
								->end()
							->end()
							->arrayNode('defaults')
								->useAttributeAsKey('name')
								->prototype('scalar')->end()
							->end()
						->end()
					->end()
				->end()
			->end();

		return $treeBuilder;
	}

	private function objectClassNodeDefinition()
	{
		$node = new ArrayNodeDefinition('object_default_class');
		$node
			->isRequired()
			->addDefaultsIfNotSet()
			->children()
				->scalarNode('image')->isRequired()->cannotBeEmpty()->end()
				->scalarNode('video')->defaultNull()->end()
				->scalarNode('audio')->defaultNull()->end()
				->scalarNode('others')->defaultNull()->end()
			->end()
		->end();
		
		return $node;
	}
	
	private function containerConfigNodeDefinition()
	{
		$node = new ArrayNodeDefinition('container_config');
		$node
			->addDefaultsIfNotSet()
			->isRequired()
			->children()
				->scalarNode('root_path')
					->isRequired()
					->cannotBeEmpty()
					->validate()
						->ifTrue(function($value){
							$i = strlen($value) - 1;
							return $value[$i] == '/' || $value[$i] == '\\';
						})
						->thenInvalid('Invalid root path "%s". The path should not end with "/" or "\".')
					->end()
					->info('The path to the root folder that will host the files.')
				->end()
				->arrayNode('data_dirnames')
					->addDefaultsIfNotSet()
					->children()
						->append($this->createStorageConfigDataDirnameNodeDefinition('image'))
						->append($this->createStorageConfigDataDirnameNodeDefinition('video'))
						->append($this->createStorageConfigDataDirnameNodeDefinition('audio'))
						->append($this->createStorageConfigDataDirnameNodeDefinition('others'))
					->end()
				->end()
				->arrayNode('entity_dirnames')
					->useAttributeAsKey('name')
					->treatNullLike([])
					->prototype('scalar')
						->validate()
							->ifTrue(function($value){
								if ($value) {
									$i = strlen($value) - 1;
									return ($value[0] == '/' || $value[0] == '\\') || ($value[$i] == '/' || $value[$i] == '\\');
								}
								return false;
							})
							->thenInvalid('Invalid dirname "%s". The path should not start and end with "/" or "\"')
						->end()
					->end()
				->end()
				->arrayNode('web_server')
					->addDefaultsIfNotSet()
					->isRequired()
					->children()
						->booleanNode('secure')
							->defaultFalse()
							->info('Use http or https protocol.')
						->end()
						->scalarNode('host')
							->defaultValue('localhost')
							->treatNullLike('localhost')
							->info('Server host.')
						->end()
						->scalarNode('port')->defaultNull()->end()
						->scalarNode('path')
							->defaultNull()
							->treatNullLike('')
							->validate()
								->ifTrue(function($value){
									if ($value) {
										$i = strlen($value) - 1;
										return $value[0] !== '/' || $value[$i] === '/';
									}
								})
								->thenInvalid('Invalid path "%s". The path must start with "/" and can not end with "/".')
							->end()
						->end()
					->end()
				->end()
			->end()
		->end();
		
		return $node;
	}
	
	private function imageNodeDefinition()
	{
		$node = new ArrayNodeDefinition('image');
		$node
			->addDefaultsIfNotSet()
			->children()
				->arrayNode('uploaded')
					->addDefaultsIfNotSet()
					->children()
						->booleanNode('detect_dominant_color')->defaultTrue()->end()
						->scalarNode('default_thumbnail_factory')->defaultNull()->end()
						->arrayNode('thumbnail_factory')
							->treatNullLike([])
							->useAttributeAsKey('name')
							->prototype('array')
								->requiresAtLeastOneElement()
								->prototype('array')
									->validate()
										->ifTrue(function($value){
											return $value['width'] === null && $value['height'] === null;
										})
										->thenInvalid('Invalid factory, "width" and "height" can not be empty.')
									->end()
									->children()
										->integerNode('width')->defaultNull()->end()
										->integerNode('height')->defaultNull()->end()
										->append($this->thumbnailModeNodeDefinition())
										->integerNode('quality')->defaultNull()->end()
									->end()
								->end()
							->end()
						->end()
					->end()
				->end()
				->arrayNode('thumbnail')
					->addDefaultsIfNotSet()
					->children()
						->append($this->thumbnailModeNodeDefinition('ratio'))
						->integerNode('quality')->defaultValue(100)->end()
					->end()
				->end()
			->end()
		->end();
		
		return $node;
	}
	
	private function thumbnailModeNodeDefinition($defaultValue = null)
	{
		$node = new ScalarNodeDefinition('mode');
		$node
			->defaultValue($defaultValue)
			->validate()
				->ifNotInArray(['inset', 'outbound', 'ratio'])
				->thenInvalid('Invalid mode "%s" for thumbnail.')
			->end()
		->end();
		
		return $node;
	}
	
	private function behaviorsNodeDefinition()
	{
		$node = new ArrayNodeDefinition('behaviors');
		$node
			->canBeDisabled()
			->addDefaultsIfNotSet()
			->treatNullLike(['enabled' => false])
			->children()
				->arrayNode('reflection')
					->addDefaultsIfNotSet()
					->children()
						->booleanNode('enable_recursive')->defaultTrue()->end()
					->end()
				->end()
				->append($this->createBehaviorsNodeDefinition('picture_coverable'))
				->append($this->createBehaviorsNodeDefinition('avatable'))
			->end()
		->end();
		
		return $node;
	}
	
	private function createStorageConfigDataDirnameNodeDefinition($name)
	{
		$node = new ScalarNodeDefinition($name);
		$node
			->defaultValue($name)
			->treatNullLike('')
			->validate()
				->ifTrue(function($value){
					if ($value) {
						$i = strlen($value) - 1;
						return ($value[0] == '/' || $value[0] == '\\') || ($value[$i] == '/' || $value[$i] == '\\');
					}
					return false;
				})
				->thenInvalid('Invalid dirname "%s". The path should not start and end with "/" or "\"')
			->end()
			->info('The name of the folder where the resource will be stored if null then in the root dir.')
		->end();
			
		return $node;
	}
	
	private function createBehaviorsNodeDefinition($name)
	{
		$node = new ArrayNodeDefinition($name);
		$node
			->addDefaultsIfNotSet()
			->canBeDisabled()
			->children()
				->arrayNode('mappings')
					->useAttributeAsKey('name')
					->prototype('array')
						->children()
							->scalarNode('image_class')->defaultNull()->end()
							->arrayNode('propertie')
								->addDefaultsIfNotSet()
								->children()
									->scalarNode('fecth_mode')
										->defaultValue('EAGER')
										->validate()
										->ifTrue(function($value){
											return !in_array(strtoupper($value), ['EAGER', 'LAZY', 'EXTRA_LAZY']);
										})
										->thenInvalid('Invalid fecth mode "%s"! The fecth mode must be EAGER, LAZY or EXTRA_LAZY.')
									->end()
								->end()
							->end()
						->end()
					->end()
				->end()
			->end()
		->end();
				
		return $node;
	}
}