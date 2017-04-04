<?php
namespace Oka\FileBundle\Utils;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * 
 * @author cedrick
 * 
 */
final class FileUtil
{
	/**
	 * @var Filesystem $fs
	 */
	private static $fs;
	
	public static function getSystemOwner()
	{
		return exec('ps axo user,comm | grep -E \'[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx\' | grep -v root | head -1 | cut -d\  -f1');
	}
	
	public static function getFs()
	{
		static::$fs = static::$fs ?: new Filesystem();
		
		return static::$fs;
	}
	
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