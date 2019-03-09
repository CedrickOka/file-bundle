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
	 * @var ContainerParameterBag $containerBag
	 */
	protected $containerBag;
	
	/**
	 * @var FileStorageHandlerInterface $fileStorageHandler
	 */
	protected $fileStorageHandler;
	
	public function __construct(ContainerParameterBag $containerBag, FileStorageHandlerInterface $fileStorageHandler)
	{
		$this->containerBag = $containerBag;
		$this->fileStorageHandler = $fileStorageHandler;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Console\Command\Command::configure()
	 */
	protected function configure()
	{
		$this->setName(static::getDefaultName())
		->setDefinition([
				new InputOption('user', null, InputOption::VALUE_OPTIONAL, 'User owner of files', null),
				new InputOption('group', null, InputOption::VALUE_OPTIONAL, 'Group owner of files', null)
		])
		->setDescription('This command creates all the containers in the file storage.')
		->setHelp(<<<EOF
The <info>%command.name%</info> command creates all the containers in the file storage :

  <info>php %command.full_name% www.exemple.com</info>

You can specify the user owner of files :

  <info>php %command.full_name% --user=www-data</info>

You can specify the owner of files :

  <info>php %command.full_name% --group=www-data</info>
EOF
				);
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
			$output->writeln(sprintf('<comment>[x]</comment> Container <info>%s</info> has been created successfully.', $value['name']));
		}
		
		$this->fileStorageHandler->close();
		$output->writeln('File storage handler has been closed.');
	}
}
