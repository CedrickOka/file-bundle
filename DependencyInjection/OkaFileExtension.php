<?php
namespace Oka\FileBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OkaFileExtension extends Extension
{
	/**
	 * @var array $doctrineDrivers
	 */
	public static $doctrineDrivers = [
			'orm' => [
					'registry' => 'doctrine',
					'tag' => 'doctrine.event_subscriber',
			],
			'mongodb' => [
					'registry' => 'doctrine_mongodb',
					'tag' => 'doctrine_mongodb.odm.event_subscriber',
			]
	];
	
	/**
	 * {@inheritDoc}
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);
		
		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yml');
		
		// Object manager configuration
		$container->setAlias('oka_file.default.doctrine_registry', new Alias(self::$doctrineDrivers[$config['db_driver']]['registry'], false));
		$container->setParameter('oka_file.model_manager_name', $config['model_manager_name']);
		$container->setParameter('oka_file.backend_type_'.$config['db_driver'], true);
		
		$definition = $container->getDefinition('oka_file.object_manager');
		$definition->setFactory([new Reference('oka_file.default.doctrine_registry'), 'getManager']);
		
		$container->setParameter('oka_file.image.default_class', $config['object_default_class']['image']);
		$container->setParameter('oka_file.video.default_class', $config['object_default_class']['video']);
		$container->setParameter('oka_file.audio.default_class', $config['object_default_class']['audio']);
		$container->setParameter('oka_file.others.default_class', $config['object_default_class']['others']);
		
		// Doctrine listeners configuration
		$this->loadDoctrineListenerConfiguration($config, $container, $loader);
		
		// Doctrine behaviors configuation
		if (true === $config['behaviors']['enabled']) {
			$this->loadDoctrineBehaviorsConfiguration($config, $container, $loader);
		}
		
		// File storage configuration
		$this->loadFileStorageConfiguration($config, $container);
		
		// File Routing configuration
		$this->loadRoutingConfiguration($config, $container);
	}
	
	protected function loadDoctrineListenerConfiguration(array $config, ContainerBuilder $container, Loader\YamlFileLoader $loader)
	{
		$loader->load('doctrine.yml');
		
		$fileListenerDefinition = $container->getDefinition('oka_file.file.doctrine_listener');
		$fileListenerDefinition->addTag(self::$doctrineDrivers[$config['db_driver']]['tag']);
		
		$ImageListenerDefinition = $container->getDefinition('oka_file.image.doctrine_listener');
		$ImageListenerDefinition->addTag(self::$doctrineDrivers[$config['db_driver']]['tag']);
	}
	
	protected function loadDoctrineBehaviorsConfiguration(array $config, ContainerBuilder $container, Loader\YamlFileLoader $loader)
	{
		$loader->load(sprintf('doctrine-behavior/%s.yml', $config['db_driver']));
		$container->setParameter('oka_file.doctrine_behaviors.reflection.is_recursive', $config['behaviors']['reflection']['enable_recursive']);
		
		foreach ($config['behaviors'] as $key => $behavior) {
			if (true === in_array($key, ['reflection', 'enabled'], SORT_REGULAR)) {
				continue;
			}
			
			$behaviorListenerDefinition = $container->getDefinition(sprintf('oka_file.doctrine_behaviors.%s_listener', preg_replace('#_+#', '', $key)));
			
			if ($behavior['enabled'] === true) {
				$behaviorListenerDefinition->replaceArgument(0, $behavior['mappings']);
				$behaviorListenerDefinition->replaceArgument(1, $config['object_default_class']['image']);
			} else {
				$behaviorListenerDefinition->clearTags();
			}
		}

// 		// PictureCoverizable behavior configuration
// 		$pictureCoverizableListenerDefinition = $container->getDefinition('oka_file.doctrine_behaviors.picturecoverizable_listener');
		
// 		if ($config['behaviors']['picture_coverizable']['enabled'] === true) {
// 			$pictureCoverizableListenerDefinition->replaceArgument(0, $config['behaviors']['picture_coverizable']['mappings']);
// 			$pictureCoverizableListenerDefinition->replaceArgument(1, $config['object_default_class']['image']);
// 		} else {
// 			$pictureCoverizableListenerDefinition->clearTags();
// 		}
		
// 		// Avatarizable behavior configuration
// 		$avatarizableListenerDefinition = $container->getDefinition('oka_file.doctrine_behaviors.avatarizable_listener');
		
// 		if ($config['behaviors']['avatarizable']['enabled'] === true) {
// 			$avatarizableListenerDefinition->replaceArgument(0, $config['behaviors']['avatarizable']['mappings']);
// 			$avatarizableListenerDefinition->replaceArgument(1, $config['object_default_class']['image']);
// 		} else {
// 			$avatarizableListenerDefinition->clearTags();
// 		}
	}
	
	protected function loadFileStorageConfiguration(array $config, ContainerBuilder $container)
	{
		$rootPath = realpath($config['container_config']['root_path']);
		$container->setParameter('oka_file.container.root_path', $rootPath);
		$container->setParameter('oka_file.container.data_dirnames', $config['container_config']['data_dirnames']);
		$container->setParameter('oka_file.container.entity_dirnames', $config['container_config']['entity_dirnames']);
		
		foreach ($config['container_config']['data_dirnames'] as $key => $dirname) {
			$container->setParameter('oka_file.container.data_dirname.'.$key, $dirname);
		}
		
		$container->setParameter('oka_file.container.web_server.host', $config['container_config']['web_server']['host']);
		$container->setParameter('oka_file.container.web_server.port', $config['container_config']['web_server']['port']);
		$container->setParameter('oka_file.container.web_server.secure', $config['container_config']['web_server']['secure']);
		
		$container->setParameter('oka_file.image.uploaded.detect_dominant_color', $config['image']['uploaded']['detect_dominant_color']);
		$container->setParameter('oka_file.image.uploaded.thumbnail_factory', $config['image']['uploaded']['thumbnail_factory']);
		$container->setParameter('oka_file.image.thumbnail.quality', $config['image']['thumbnail']['quality']);
		$container->setParameter('oka_file.image.thumbnail.mode', $config['image']['thumbnail']['mode']);
	}
	
	protected function loadRoutingConfiguration(array $config, ContainerBuilder $container)
	{
		$fileUriLoaderDefinition = $container->getDefinition('oka_file.file_uri.routing_loader');
		
		if (!empty($config['routing'])) {
			$defaultHost = $config['container_config']['web_server']['host'];
			$defaultHost .= $config['container_config']['web_server']['port'] !== null ? ':' . $config['container_config']['web_server']['port'] : '';
			$defaultScheme = $config['container_config']['web_server']['secure'] === true ? 'https' : 'http';
			
			foreach ($config['routing'] as $name => $route) {
				if ($route['host'] === null) {
					$config['routing'][$name]['host'] = $defaultHost;
				}
				if ($route['scheme'] === null) {
					$config['routing'][$name]['scheme'] = $defaultScheme;
				}
			}
			
			$fileUriLoaderDefinition->replaceArgument(0, $config['routing']);
		} else {
			$fileUriLoaderDefinition->clearTags();
		}
	}
}
