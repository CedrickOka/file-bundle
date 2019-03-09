<?php
namespace Oka\FileBundle\DependencyInjection;

use Oka\FileBundle\Doctrine\FileListener;
use Oka\FileBundle\Model\FileStorageHandlerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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
		
		$this->storageLoad($config['storage'], $container, $loader);
		$this->doctrineLoad($config, $container, $loader);
		
		if (true === $config['behaviors']['enabled']) {
			$this->behaviorsLoad($config, $container, $loader);
		}
		
		$loader->load('services.yml');
	}
	
	protected function storageLoad(array $config, ContainerBuilder $container, Loader\YamlFileLoader $loader)
	{
		$loader->load('storage.yml');
		
		$container->setParameter('oka_file.storage.root_path', $config['root_path']);
		$container->setParameter('oka_file.storage.webserver', $config['webserver']);
		$container->setParameter('oka_file.storage.image.dominant_color', $config['image']['dominant_color']);
		$container->setParameter('oka_file.storage.image.thumbnail_factory', $config['image']['thumbnail_factory']);
		
		$this->loadStorageHandler($config['handler_id'], $container);
		$this->loadContainers($config['containers'], $container);
	}
	
	protected function loadStorageHandler($handlerId, ContainerBuilder $container)
	{
		$definition = $container->findDefinition($handlerId);
		$reflClass = new \ReflectionClass($definition->getClass());
		
		if (false === $reflClass->implementsInterface(FileStorageHandlerInterface::class)) {
			throw new InvalidConfigurationException(sprintf('The handler service "%s" id must implemented "%s" interface.', $handlerId, FileStorageHandlerInterface::class));
		}
		
		$container->setAlias('oka_file.storage_handler', new Alias($handlerId, true));
		
		$imageManipulator = $container->findDefinition('oka_file.storage.image_manipulator');
		$imageManipulator->replaceArgument(0, new Reference('oka_file.storage_handler'));
	}
	
	protected function loadContainers(array $containers, ContainerBuilder $container)
	{
		$definition = $container->getDefinition('oka_file.container_parameter_bag');
		$definition->replaceArgument(0, $containers);
	}
	
	protected function doctrineLoad(array $config, ContainerBuilder $container, Loader\YamlFileLoader $loader)
	{
		$loader->load('doctrine.yml');
		
		$container->setParameter('oka_file.db_driver', $config['db_driver']);
		$container->setParameter('oka_file.backend_type_'.$config['db_driver'], true);
		$container->setParameter('oka_file.model_manager_name', $config['model_manager_name']);
		$container->setAlias('oka_file.doctrine_registry.default', new Alias(self::$doctrineDrivers[$config['db_driver']]['registry'], false));
		
		$definition = $container->getDefinition('oka_file.object_manager');
		$definition->setFactory([new Reference('oka_file.doctrine_registry.default'), 'getManager']);
		
		$fileListener = $container->getDefinition(FileListener::class);
		$fileListener->addTag(self::$doctrineDrivers[$config['db_driver']]['tag']);
	}
	
	protected function behaviorsLoad(array $config, ContainerBuilder $container, Loader\YamlFileLoader $loader)
	{
		$loader->load(sprintf('doctrine-behavior/%s.yml', $config['db_driver']));
		$container->setParameter('oka_file.doctrine_behaviors.reflection.is_recursive', $config['behaviors']['reflection']['enable_recursive']);
		
		foreach ($config['behaviors'] as $key => $behavior) {
			if (true === in_array($key, ['reflection', 'enabled'], SORT_REGULAR)) {
				continue;
			}
			
			$behaviorListener = $container->getDefinition(sprintf('oka_file.doctrine_behaviors.%s_listener', preg_replace('#_+#', '', $key)));
			
			if (true === $behavior['enabled'] && false === empty($behavior['mappings'])) {
				$behaviorListener->replaceArgument(0, $behavior['mappings']);
			} else {
				$behaviorListener->clearTags();
			}
		}
	}
}
