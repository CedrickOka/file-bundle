<?php
namespace Oka\FileBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OkaFileExtension extends Extension
{
	/**
	 * {@inheritDoc}
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);
		
		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yml');
		$loader->load('doctrine.yml');
		
		// Entity and Object Manager Configuration
// 		$container->setParameter('oka_file.model_manager_name', $config['model_manager_name']);
		$container->setAlias('oka_file.doctrine_registry', new Alias('doctrine', false));
		$objectManagerDefintion = $container->getDefinition('oka_file.object_manager');
		$objectManagerDefintion->replaceArgument(0, $config['model_manager_name']);
		$objectManagerDefintion->setFactory([new Reference('oka_file.doctrine_registry'), 'getManager']);
		
		$container->setParameter('oka_file.image.default_class', $config['object_default_class']['image']);
		$container->setParameter('oka_file.video.default_class', $config['object_default_class']['video']);
		$container->setParameter('oka_file.audio.default_class', $config['object_default_class']['audio']);
		$container->setParameter('oka_file.others.default_class', $config['object_default_class']['others']);
		
		// Store configuration
		$rootPath = realpath($config['container_config']['root_path']);
		$container->setParameter('oka_file.container.root_path', $rootPath);
		
		$container->setParameter('oka_file.container.data_dirnames', $config['container_config']['data_dirnames']);
		foreach ($config['container_config']['data_dirnames'] as $key => $dirname) {
			$container->setParameter('oka_file.container.data_dirname.'.$key, $dirname);
		}
		
		$container->setParameter('oka_file.container.web_server.host', $config['container_config']['web_server']['host']);
		$container->setParameter('oka_file.container.web_server.port', $config['container_config']['web_server']['port']);
		$container->setParameter('oka_file.container.web_server.secure', $config['container_config']['web_server']['secure']);
		
		// Image configuration
		$container->setParameter('oka_file.image.uploaded.detect_dominant_color', $config['image']['uploaded']['detect_dominant_color']);
		$container->setParameter('oka_file.image.uploaded.thumbnail_factory', $config['image']['uploaded']['thumbnail_factory']);
		$container->setParameter('oka_file.image.thumbnail.quality', $config['image']['thumbnail']['quality']);
		$container->setParameter('oka_file.image.thumbnail.mode', $config['image']['thumbnail']['mode']);
		
		if ($config['behaviors']['enabled'] === true) {
			$loader->load('behaviors.yml');
			
			$container->setParameter('oka_file.doctrine_behaviors.reflection.is_recursive', $config['behaviors']['reflection']['enable_recursive']);
			$pictureCoverableListenerDefinition = $container->getDefinition('oka_file.doctrine_behaviors.picturecoverable_listener');
			
			// PictureCoverable Behavior Configuration
			if ($config['behaviors']['picture_coverable']['enabled']) {
				$pictureCoverableListenerDefinition->replaceArgument(0, $config['behaviors']['picture_coverable']['mappings']);
				$pictureCoverableListenerDefinition->replaceArgument(1, $config['object_default_class']['image']);				
			} else {
				$pictureCoverableListenerDefinition->clearTags();
			}
		}

		// File Routing Configuration
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