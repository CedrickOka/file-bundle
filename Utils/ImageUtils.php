<?php
namespace Oka\FileBundle\Utils;

use Oka\FileBundle\Model\FileInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Oka\FileBundle\Model\ImageInterface;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
final class ImageUtils
{
	/**
	 * Gets image from uploaded file
	 * 
	 * @param UploadedFile $file
	 * @param string $class The class name
	 * @throws \LogicException
	 * @return \Oka\FileBundle\Model\ImageInterface
	 */
	public static function getImageFromUploadedFile(UploadedFile $file, $class)
	{
		$image = new $class();
		
		if (!$image instanceof FileInterface && !$image instanceof ImageInterface) {
			throw new \LogicException(sprintf('Unable to convert object from class "Symfony\Component\HttpFoundation\File\UploadedFile" to object of class "%s".', $class));
		}
		
		$image->setUploadedFile($file);
		
		return $image;
	}
	
	/**
	 * Gets image from file path
	 * 
	 * @param string $path The absolute path with the name of file
	 * @param string $originalName The file name
	 * @param string $class The class name
	 * @throws \LogicException
	 * @return \Oka\FileBundle\Model\ImageInterface
	 */
	public static function getImageFromPath($path, $originalName, $class)
	{
		$file = new UploadedFile($path, $originalName, finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path), filesize($path), UPLOAD_ERR_OK, true);
		
		return self::getImageFromUploadedFile($file, $class);
	}
	
	/**
	 * Gets GIF for image placeholder
	 * 
	 * @param string $color
	 * @return string
	 */
	public static function getImageGIFPlaceholder($color)
	{
		$header 					= '474946383961';
		$logicalScreenDescriptor 	= '01000100800100';
		$imageDescriptor 			= '2c000000000100010000';
		$imageData 					= '0202440100';
		// If you want to define trailer, define it with the value '3b';
		
		$gif = implode([
				$header,
				$logicalScreenDescriptor,
				$color,
				'000000',
				$imageDescriptor,
				$imageData,
				// Add the trailer value here
		]);
		
		return 'data:image/gif;base64,' . base64_encode(hex2bin($gif));
	}
}
