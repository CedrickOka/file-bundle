<?php
namespace Oka\FileBundle\Tests\Service;

use Oka\FileBundle\Service\UploadedImageManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class UploadedImageManagerTest extends KernelTestCase
{
	/**
	 * @var UploadedImageManager $uploadedImageManager
	 */
	protected $uploadedImageManager;
	
	public function setUp()
	{
		static::bootKernel();
		$this->uploadedImageManager = static::$kernel->getContainer()->get('oka_file.uploaded_image.manager');
	}
	
	public function testFindImageDominantColor()
	{
		$colorRGB = $this->uploadedImageManager->findImageDominantColor(__DIR__.'/../Resources/images/test_dominant_color.jpg');
		$this->assertEquals('ead087', $colorRGB);
		
		$colorRGB = $this->uploadedImageManager->findImageDominantColor(__DIR__.'/../Resources/images/coke-can.jpg', UploadedImageManager::DOMINANT_COLOR_METHOD_KMEANS);
		$this->assertEquals('b21812', $colorRGB);
	}
}
