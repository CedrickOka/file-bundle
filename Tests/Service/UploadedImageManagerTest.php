<?php
use Oka\FileBundle\Service\UploadedImageManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * 
 * @author cedrick
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
	}
}