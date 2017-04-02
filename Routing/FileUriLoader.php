<?php
namespace Oka\FileBundle\Routing;

use Oka\FileBundle\Model\ImageInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * 
 * @author cedrick
 * 
 */
class FileUriLoader implements LoaderInterface
{
	/**
	 * @var boolean $loaded
	 */
	private $loaded = false;
	
	/**
	 * @var array $routesDefinition
	 */
	private $routesDefinition = [];
	
	/**
	 * @var string $environment
	 */
	private $environment;
	
	/**
	 * @var string $thumbnailMode
	 */
	private $thumbnailMode;
	
	/**
	 * @var integer $thumbnailQuality
	 */
	private $thumbnailQuality;
	
	public function __construct(array $routesDefinition = [], $environment, $thumbnailMode, $thumbnailQuality)
	{
		$this->routesDefinition = $routesDefinition;
		$this->environment = $environment;
		$this->thumbnailMode = $thumbnailMode;
		$this->thumbnailQuality = $thumbnailQuality;
	}
	
	public function setResolver(LoaderResolverInterface $resolver)
	{
		// TODO Auto-generated method stub
	}
	
	public function getResolver()
	{
		// TODO Auto-generated method stub
	}
	
	public function supports($resource, $type = null)
	{
		return 'file' === $type;
	}
	
	public function load($resource, $type = null)
	{
		if (true === $this->loaded) {
			throw new \RuntimeException('Do not add this loader twice');
		}
		
		$routes = new RouteCollection();		
		$prefix = $this->environment == 'dev' ? '/..' : '';
		
		foreach ($this->routesDefinition as $name => $route) {
			$class = $route['file_class'];
			$reflClass = new \ReflectionClass($class);
			$basePath = $prefix . $route['prefix'];
			
			$routes->add('oka_file_extra_'.$name, new Route(
					$basePath . '/{filename}', 
					$route['defaults'], 
					['methods' => 'GET'], 
					[], 
					$route['host'], 
					$route['scheme']
			));
			
			if ($reflClass->implementsInterface(ImageInterface::class)) {
				$routes->add('oka_file_extra_'.$name.'_thumbnail', new Route(
						$basePath . '/{mode}/{quality}/{size}/{filename}',
						array_merge(['mode' => $this->thumbnailMode, 'quality' => $this->thumbnailQuality], $route['defaults']),
						['methods' => 'GET'],
						[],
						$route['host'],
						$route['scheme']
				));
			}
		}		
		$this->loaded = true;
		
		return $routes;
	}
}