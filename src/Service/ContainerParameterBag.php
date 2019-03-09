<?php
namespace Oka\FileBundle\Service;

use Oka\FileBundle\Model\FileInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 *
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class ContainerParameterBag extends ParameterBag
{
	public function get($key, $default = null)
	{
		return parent::get($key instanceof FileInterface ? get_class($key) : $key, $default);
	}
	
	public function set($key, $value)
	{
		parent::set($key instanceof FileInterface ? get_class($key) : $key, $value);
	}
	
	public function has($key)
	{
		return parent::has($key instanceof FileInterface ? get_class($key) : $key);
	}
}
