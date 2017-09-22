<?php
namespace Oka\FileBundle\Tests\Utils;

use Oka\FileBundle\Utils\FileUtil;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class FileUtilTest extends KernelTestCase
{
	public function testGetSystemOwner()
	{
		$this->assertEquals('cedrick', FileUtil::getSystemOwner());
	}
}
