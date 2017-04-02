<?php
namespace Oka\FileBundle\Model;

/**
 * 
 * @author cedrick
 * 
 */
interface FileManagerInterface
{
	public function getClass();
	
	public function getObjectManager();
	
	/**
	 * @return FileInterface
	 */
	public function createFile();
	
	public function updateFile(FileInterface $file, $andFlush = true);
	
	public function deleteFile(FileInterface $file);
	
	public function findFile($id);
	
	public function findFileBy(array $criteria);
	
	public function findFilesBy(array $criteria, array $order = array(), $limit = null, $offset = null);
	
	public function findFiles();
}