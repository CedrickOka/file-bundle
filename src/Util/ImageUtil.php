<?php
namespace Oka\FileBundle\Util;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
final class ImageUtil
{
	/**
	 * @param string $filename
	 * @throws \RuntimeException
	 * @return \Imagick|\Gmagick
	 */
	public static function createXMagick($filename = null) {
		if (true === class_exists('Imagick')) {
			return new \Imagick($filename);
		}
		
		if (true === class_exists('Gmagick')) {
			return new \Gmagick($filename);
		}
		
		throw new \RuntimeException('Unable to load Imagick or Gmagick class');
	}
	
	/**
	 * @param string $name
	 * @throws \RuntimeException
	 * @return mixed
	 */
	public static function getXMagickConstant($name) {
		if (true === class_exists('Imagick')) {
			$reflClass = new \ReflectionClass('Imagick');
		}
		
		if (true === class_exists('Gmagick')) {
			$reflClass = new \ReflectionClass('Gmagick');
		}
		
		if (false === isset($reflClass)) {
			throw new \RuntimeException('Unable to load Imagick or Gmagick class');
		}
		
		return $reflClass->getConstant($name);
	}
	
	/**
	 * Create Imagine class instance
	 *
	 * @return \Imagine\Image\ImagineInterface
	 */
	public static function createImagine() {
		if (true === class_exists('Imagick')) {
			return new \Imagine\Imagick\Imagine();
		}
		
		if (true === class_exists('Gmagick')) {
			return new \Imagine\Gmagick\Imagine();
		}
		
		return new \Imagine\Gd\Imagine();
	}
}
