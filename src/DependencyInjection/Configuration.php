<?php
namespace Oka\FileBundle\DependencyInjection;

use Oka\FileBundle\Model\FileInterface;
use Oka\FileBundle\Model\ImageInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

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
				->enumNode('db_driver')
					->cannotBeEmpty()
					->values(['mongodb', 'orm'])
					->defaultValue('orm')
				->end()
				
				->scalarNode('model_manager_name')->defaultNull()->end()
				
				->arrayNode('storage')
					->addDefaultsIfNotSet()
					->validate()
						->ifTrue(static function($v){
							foreach ($v['containers'] as $class => $value) {
								$reflClass = new \ReflectionClass($class);
								
								switch (true) {
									case $reflClass->implementsInterface(FileInterface::class):
										continue;
										
									case $reflClass->implementsInterface(ImageInterface::class):
										continue;
										
									default:
										return true;
								}
							}
							return false;
						})
						->thenInvalid('Invalid container manager configuration.')
					->end()
					->children()
					
						->scalarNode('handler_id')->defaultValue('oka_file.storage_handler.default')->end()
						->scalarNode('root_path')->cannotBeEmpty()->defaultValue('%kernel.project_dir%')->end()
						
						->arrayNode('webserver')
							->addDefaultsIfNotSet()
							->treatNullLike([])
							->children()
								->scalarNode('scheme')->defaultValue('http')->end()
								->scalarNode('user')->defaultNull()->end()
								->scalarNode('password')->defaultNull()->end()
								->scalarNode('host')->defaultValue('localhost')->end()
								->integerNode('port')
									->defaultValue(80)
									->treatNullLike(80)
								->end()
								->scalarNode('path')
									->defaultValue('/')
									->treatNullLike('/')
									->validate()
										->ifTrue(static function($value){
											if ($value) {
												if ('/' === $value) {
													return false;
												}
												$i = strlen($value) - 1;
												
												return '/' !== $value[0] || '/' === $value[$i];
											}
										})
										->thenInvalid('Invalid path "%s". The path must start with "/" and can not end with "/".')
									->end()
								->end()
								->scalarNode('query')->defaultNull()->end()
							->end()
						->end()
						
						->arrayNode('image')
							->addDefaultsIfNotSet()
							->children()
								->append($this->getDominantColorNode())
								->append($this->getThumbnailFactoryNode())
							->end()
						->end()
						
						->arrayNode('containers')
							->useAttributeAsKey('class')
							->arrayPrototype()
								->addDefaultsIfNotSet()
								->children()
								
									->scalarNode('class')
										->cannotBeEmpty()
										->info('The file object class')
									->end()
									
									->scalarNode('name')
										->isRequired()
										->cannotBeEmpty()
										->info('The object store container name')
										->beforeNormalization()
											->always(static function($v){
												return trim($v, '/');
											})
										->end()
									->end()
									
									->append($this->getThumbnailFactoryNode())
									->append($this->getDominantColorNode())
									
								->end()
							->end()
						->end()
					->end()
				->end()
				
				->arrayNode('behaviors')
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
						
						->arrayNode('picture_coverizable')
							->addDefaultsIfNotSet()
							->canBeDisabled()
							->children()
								->append($this->getBehaviorMappingNode())
							->end()
						->end()
						
						->arrayNode('avatarizable')
							->addDefaultsIfNotSet()
							->canBeDisabled()
							->children()
								->append($this->getBehaviorMappingNode())
							->end()
						->end()
						
					->end()
				->end()
				
			->end();
		
		return $treeBuilder;
	}
	
	private function getDominantColorNode()
	{
		$node = new ArrayNodeDefinition('dominant_color');
		$node
			->canBeDisabled()
			->addDefaultsIfNotSet()
			->treatNullLike(['enabled' => false, 'method' => 'quantize'])
			->treatFalseLike(['enabled' => false, 'method' => 'quantize'])
			->treatTrueLike(['enabled' => true, 'method' => 'quantize'])
			->children()
				->enumNode('method')
					->values(['k-means', 'quantize'])
					->defaultValue('quantize')
				->end()
				->arrayNode('options')
					->variablePrototype()->end()
				->end()
			->end()
		->end();
		
		return $node;
	}
	
	private function getThumbnailFactoryNode()
	{
		$node = new ArrayNodeDefinition('thumbnail_factory');
		$node
			->treatNullLike([])
			->arrayPrototype()
			->addDefaultsIfNotSet()
			->validate()
				->ifTrue(function($value){
					return null === $value['width'] && null === $value['height'];
				})
				->thenInvalid('Invalid factory, "width" and "height" can not be empty.')
			->end()
			->children()
				->integerNode('quality')->defaultValue(100)->end()
				->integerNode('width')->defaultNull()->end()
				->integerNode('height')->defaultNull()->end()
				->enumNode('mode')
					->values(['inset', 'outbound', 'ratio'])
					->defaultValue('ratio')
				->end()
			->end()
		->end();
		
		return $node;
	}
	
	private function getBehaviorMappingNode()
	{
		$node = new ArrayNodeDefinition('mappings');
		$node
			->useAttributeAsKey('name')
			->prototype('array')
				->children()
					->scalarNode('target_object')->isRequired()->cannotBeEmpty()->end()
					->booleanNode('embedded')
						->defaultTrue()
						->info('Used only with the database driver `mongodb`.')
					->end()
					->scalarNode('fetch')
						->defaultValue('EAGER')
						->validate()
							->ifTrue(function($value){
								return !in_array(strtoupper($value), ['EAGER', 'LAZY', 'EXTRA_LAZY']);
							})
							->thenInvalid('Invalid fetch mode "%s"! The fetch mode must be EAGER, LAZY or EXTRA_LAZY.')
						->end()
					->end()
					->arrayNode('options')
						->treatNullLike([])
						->prototype('scalar')->end()
					->end()
				->end()
			->end()
		->end();
		
		return $node;
	}
}
