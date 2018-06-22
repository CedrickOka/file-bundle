<?php
namespace Oka\FileBundle\Utils;

use Symfony\Component\Filesystem\Filesystem;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
final class FileUtil
{
	/**
	 * @var Filesystem $fs
	 */
	private static $fs;
	
	/**
	 * @return mixed
	 */
	public static function getSystemOwner()
	{
		return posix_getpwuid(posix_geteuid())['name'];
	}
	
	public static function findParentDirectoyThatExists($path)
	{
		$fs = static::getFs();
		
		while (!$fs->exists($path)) {
			$path = dirname($path);
		}
		
		return $path;
	}
	
	/**
	 * @return \Symfony\Component\Filesystem\Filesystem
	 */
	public static function getFs()
	{
		static::$fs = static::$fs ?: new Filesystem();
		
		return static::$fs;
	}
	
	/**
	 * @param string $dirs
	 * @param integer $mode
	 * @param string $owner
	 * @param string $group
	 * @param string $recursive
	 */
	public static function mkdir($dirs, $mode = 0755, $owner = null, $group = null, $recursive = true)
	{
		$owner = $owner ?: self::getSystemOwner();
		
		static::getFs()->mkdir($dirs, $mode);
		static::getFs()->chown($dirs, $owner, $recursive);
		static::getFs()->chgrp($dirs, $group ?: $owner, $recursive);
	}
	
	/**
	 * @param string $rootPath
	 * @param string $realPath
	 * @param string $host
	 * @param integer $port
	 * @param string $path
	 * @param boolean $secure
	 * @return string
	 * @ignore
	 */
	public static function translateRealPathInUri($rootPath, $realPath, $host, $port, $path, $secure = false) {}
}
