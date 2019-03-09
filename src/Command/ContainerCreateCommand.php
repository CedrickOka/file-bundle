<?php
namespace Oka\FileBundle\Command;

use Oka\FileBundle\Model\FileStorageHandlerInterface;
use Oka\FileBundle\Service\ContainerParameterBag;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class ContainerCreateCommand extends Command
{
	protected static $defaultName = 'okafile:container:create';
	
	/**
	 * @var string $rootPath
	 */
	protected $rootPath;
	
	/**
	 * @var ContainerParameterBag $containerBag
	 */
	protected $containerBag;
	
	/**
	 * @var FileStorageHandlerInterface $fileStorageHandler
	 */
	protected $fileStorageHandler;
	
	public function __construct(string $rootPath, ContainerParameterBag $containerBag, FileStorageHandlerInterface $fileStorageHandler)
	{
		$this->rootPath = $rootPath;
		$this->containerBag = $containerBag;
		$this->fileStorageHandler = $fileStorageHandler;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Console\Command\Command::configure()
	 */
	protected function configure()
	{
		$this->setName(static::$defaultName)
			 ->setDescription('Create file container')
			 ->setDefinition([
			 		new InputOption('user', null, InputOption::VALUE_OPTIONAL, 'Owner of files', null),
			 		new InputOption('group', null, InputOption::VALUE_OPTIONAL, 'Group of files', null)
			 ])
			 ->setHelp('Cette commande permet de générer le systeme de fichier.');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Console\Command\Command::execute()
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->fileStorageHandler->open();
		$output->writeln('File storage handler has been opened.');
		
		foreach ($this->containerBag->all() as $value) {
			$this->fileStorageHandler->createContainer($value['name'], $input->getOption('user'), $input->getOption('group'));
			$output->writeln(sprintf('Container <info>%s</info> has been created', $value['name']));
		}
	}
}
