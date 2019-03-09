<?php
namespace Oka\FileBundle\Tests\Service;

use Oka\FileBundle\Model\Image as BaseImage;
use Oka\FileBundle\Service\ImageManipulator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Oka\FileBundle\Service\NativeFileStorageHandler;
use Oka\FileBundle\Service\ContainerParameterBag;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class ImageManipulatorTest extends KernelTestCase
{
	/**
	 * @var ImageManipulator $imageManipulator
	 */
	protected $imageManipulator;
	
	public function setUp()
	{
		$this->imageManipulator = new ImageManipulator(new NativeFileStorageHandler(__DIR__, [], new ContainerParameterBag()));
	}
	
	public function testGetDominantColor()
	{
		$image = new Image();
		
// 		$path = __DIR__.'/../Resources/images/image1.jpg';
// 		$image->setUploadedFile(new UploadedFile($path, 'image1.jpg', 'image/jpeg', filesize($path), UPLOAD_ERR_OK, true));
		
// 		$this->assertEquals('b21812', $this->imageManipulator->getDominantColor($image, ImageManipulator::DOMINANT_COLOR_METHOD_KMEANS));
		
		$path = __DIR__.'/../Resources/images/image2.jpg';
		$image->setUploadedFile(new UploadedFile($path, 'image1.jpg', 'image/jpeg', filesize($path), UPLOAD_ERR_OK, true));
		
		$this->assertEquals('f6d344', $this->imageManipulator->getDominantColor($image));
	}
}

class Image extends BaseImage {}
