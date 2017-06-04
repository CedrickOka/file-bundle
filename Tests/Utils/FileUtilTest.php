<?php
namespace Oka\FileBundle\Tests\Utils;

use Oka\FileBundle\Utils\FileUtil;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * 
 * @author cedrick
 * 
 */
class FileUtilTest extends KernelTestCase
{
	public function testGetSystemOwner()
	{
		$this->assertEquals('cedrick', FileUtil::getSystemOwner());
	}
}