<?php
namespace Oka\FileBundle\Command;

use Oka\FileBundle\Model\File;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * 
 * @author cedrick
 * 
 */
class GenerateFileSystemCommand extends ContainerAwareCommand
{
	/**
	 * Configure
	 * 
	 * @see \Symfony\Component\Console\Command\Command::configure()
	 */
	protected function configure()
	{
		$this->setName('oka:file:generate:filesystem')
			 ->setDescription('Generate File system')
			 ->setDefinition([
			 		new InputOption('user', null, InputOption::VALUE_OPTIONAL, 'Owner of files', null),
			 		new InputOption('group', null, InputOption::VALUE_OPTIONAL, 'Group of files', null)//,
// 			 		new InputOption('mode', null, InputOption::VALUE_OPTIONAL, 'Directory mode', 0755)
			 ])
			 ->setHelp(<<<EOF
Cette commande permet de generer le systeme de fichier.
EOF
				);
	}
	
	/**
	 * Execute
	 * 
	 * @see \Symfony\Component\Console\Command\Command::execute()
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
		$fs = new Filesystem();
		$container = $this->getContainer();
		$rootDirRaw = $container->getParameter('oka_file_manager.root_dir.raw');
		$pictureClass = $container->getParameter('oka_file_manager.picture.default_class');
		$em = $container->get('doctrine')->getManager($container->getParameter('oka_file_manager.entity_manager_name'));
		$picture = new $pictureClass();
		
		if ($picture instanceof File) {
			$picture->setId(0);
			$em->persist($picture);
			
			$mode = 0755;
			$user = $input->getOption('user') ?: $pictureClass::FILES_OWNER;
			$group = $input->getOption('group') ?: $pictureClass::FILES_GROUP;
			
			$output->writeln('Creating filesystem');
			$output->writeln(sprintf('Creating root directory into <comment>%s</comment>', $rootDirRaw));
			
			if (!$fs->exists($rootDirRaw)) {
				$fs->mkdir($rootDirRaw, $mode);
				$picture->setRootDir(realpath($rootDirRaw).'/');
			}
			
			$output->writeln(sprintf('Creating directory for <comment>%s</comment> into <comment>%s</comment>', $pictureClass, $picture->getUploadRootDir()));
			
			if (!$fs->exists($picture->getUploadRootDir())) {
				$fs->mkdir($picture->getUploadRootDir(), $mode);
			}
			
			$output->writeln(sprintf('Configuring user [<info>%s</info>] and group [<info>%s</info>] for filesystem', $user, $group));
			$fs->chown($picture->getRootDir(), $user, true);
			$fs->chgrp($picture->getRootDir(), $group, true);
			$em->detach($picture);
		}
	}
}