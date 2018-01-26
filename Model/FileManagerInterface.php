<?php
namespace Oka\FileBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface FileManagerInterface
{
	/**
	 * @return string
	 */
	public function getClass();
	
	/**
	 * @return string
	 */
	public function setClass($class);
	
	/**
	 * @return ObjectManager
	 */
	public function getObjectManager();
	
	/**
	 * @return FileInterface
	 */
	public function createFile();
	
	/**
	 * @param FileInterface $file
	 * @param boolean $andFlush
	 * @return FileInterface
	 */
	public function updateFile(FileInterface $file, $andFlush = true);
	
	/**
	 * @param FileInterface $file
	 */
	public function deleteFile(FileInterface $file);
	
	/**
	 * @param mixed $id
	 * @return FileInterface
	 */
	public function findFile($id);
	
	/**
	 * @param array $criteria
	 * @return FileInterface
	 */
	public function findFileBy(array $criteria);
	
	/**
	 * @param array $criteria
	 * @param array $order
	 * @param integer $limit
	 * @param integer $offset
	 * @return array
	 */
	public function findFilesBy(array $criteria, array $order = [], $limit = null, $offset = null);
	
	/**
	 * @return array
	 */
	public function findFiles();
}
