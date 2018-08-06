<?php
namespace Oka\FileBundle\Command;

use Oka\FileBundle\Utils\FileUtil;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class ConfigureContainerCommand extends ContainerAwareCommand
{
	/**
	 * Configure
	 * 
	 * @see \Symfony\Component\Console\Command\Command::configure()
	 */
	protected function configure()
	{
		$owner = FileUtil::getSystemOwner();
		
		$this->setName('okafile:configure:container')
			 ->setDescription('Configure file container')
			 ->setDefinition([
			 		new InputOption('user', null, InputOption::VALUE_OPTIONAL, 'Owner of files', $owner),
			 		new InputOption('group', null, InputOption::VALUE_OPTIONAL, 'Group of files', $owner)
			 ])
			 ->setHelp('Cette commande permet de generer le systeme de fichier.');
	}
	
	/**
	 * Execute
	 * 
	 * @see \Symfony\Component\Console\Command\Command::execute()
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
		$container = $this->getContainer();
		$rootPath = $container->getParameter('oka_file.container.root_path');
		$dataDirnames = $container->getParameter('oka_file.container.data_dirnames');
		
		$mode = 0755;
		$user = $input->getOption('user');
		$group = $input->getOption('group');
		
		$output->writeln('File container configuration');
		$output->writeln(sprintf('Configuring user [<info>%s</info>] and group [<info>%s</info>] for filesystem', $user, $group));
		
		if (!FileUtil::getFs()->exists($rootPath)) {
			$output->writeln(sprintf('Creating root directory into <comment>%s</comment>', $rootPath));
			FileUtil::mkdir($rootPath, $mode, $user, $group);
		}
		
		foreach ($dataDirnames as $key => $dirname) {
			$dataPath = $rootPath . ($dirname ? '/' . $dirname : '');
			
			if (!FileUtil::getFs()->exists($dataPath)) {
				$output->writeln(sprintf('Creating directory for <comment>%s</comment> into <comment>%s</comment>', $key, $dataPath));
				FileUtil::mkdir($dataPath, $mode, $user, $group);
			}
		}
	}
}
