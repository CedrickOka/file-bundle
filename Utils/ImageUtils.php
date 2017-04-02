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
abstract class ImageUtils
{
	/**
	 * @param string $path
	 * @return string|mixed
	 */
	public static function regularizeTrailingSlash($path)
	{
		$path = preg_replace('#\\\\#', '/', $path);
		
		if (preg_match('#^(.*)/$#', $path) !== 1) {
			$path .= '/';
		}
		
		return $path;
	}
	
	/**
	 * @param string $sourceDirectory
	 * @param string $fileName
	 * @param string $uploadDir
	 * @param string $uploadRootDir
	 * @param string $domain
	 * @param string $size
	 * @param string $defaultDir
	 * @throws \RuntimeException
	 * @return string
	 */
	public static function translateAbsolutePathInWebPath($sourceDirectory, $fileName, $uploadDir, $uploadRootDir, $domain, $size = '', $defaultDir = 'default/')
	{
		$fs = new Filesystem();
		$absolutePath = self::regularizeTrailingSlash($sourceDirectory).$fileName;
		
		if ($fs->exists($absolutePath)) {
			$domain = self::regularizeTrailingSlash($domain);
			$uploadDir = self::regularizeTrailingSlash($uploadDir);
			$defaultDir = self::regularizeTrailingSlash($defaultDir);
			$uploadRootDir = self::regularizeTrailingSlash($uploadRootDir);
			
			$targetDirectory = $uploadRootDir.$defaultDir;
			$path = self::regularizeTrailingSlash($targetDirectory.$size).$fileName;
			$url = self::regularizeTrailingSlash($domain.$uploadDir.$defaultDir.$size).$fileName;
			if ($fs->exists($path)) {
				return $url;
			}
				
			$imagine = (!class_exists('Imagick')) ? new \Imagine\Gd\Imagine() : new \Imagine\Imagick\Imagine();
			$picture = $imagine->open($absolutePath);
			if ($size) {
				$boxSize = preg_split('#x#i', $size);
				$picture = $picture->thumbnail(new Box((int) $boxSize[0], (int) $boxSize[1]), ImageInterface::THUMBNAIL_OUTBOUND);
			}
				
			if (!$fs->exists($targetDirectory.$size)) {
				$fs->mkdir($targetDirectory.$size, 0700);
			}
			$picture->save($path);
				
			return $url;
		}
	
		throw new \RuntimeException(sprintf('Unable to open image %s', $absolutePath));
	}
}