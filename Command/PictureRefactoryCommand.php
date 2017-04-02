<?php
namespace Oka\FileBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 
 * @author cedrick
 * 
 */
class PictureRefactoryCommand extends ContainerAwareCommand
{
	/**
	 * Configure
	 *
	 * @see \Symfony\Component\Console\Command\Command::configure()
	 */
	protected function configure()
	{
		$this->setName('oka:file:picture:refactory')
			 ->setDescription('Refactory picture uploaded')
			 ->setDefinition(array(
			 		new InputArgument('pictureClass', InputArgument::OPTIONAL, 'Picture class has refactory', null),
			 ))
			 ->setHelp(<<<EOF
Aide pour la commande <info>oka:filemanager:picture:refactory</info>.

Cette commande permet de reformatter les images dans les tailles definies dans les
fichiers de configuration.
	
<info>php app/console oka:filemanager:picture:refactory</info>
EOF
);
	}
	
	/**
	 * Execute
	 *
	 * @see \Symfony\Component\Console\Command\Command::execute()
	 */
	public function execute(InputInterface $input, OutputInterface $output) {
		$pictureManager = $this->getContainer()->get('oka_file_manager.picture_manager');
		if ($pictureClass = $input->getArgument('pictureClass')) {
			$pictureManager->setClass($pictureClass);
		}
		
		if ($pictures = $pictureManager->findPictures()) {
			$output->writeln('Refactoring picture...');
			$output->writeln('');
			$uploadManager = $this->getContainer()->get('oka_file_manager.upload_manager');
			foreach ($pictures as $picture) {
				$ctrl = $uploadManager->reFactory($picture);
				if (0 === $ctrl) {
					goto END;
				} elseif (true === $ctrl && OutputInterface::VERBOSITY_NORMAL === $output->getVerbosity()) {
					$date = date('H:i:s');
					$output->writeln('<comment>'.$date.'</comment> <info>[picture]</info> '.$picture->getAbsolutePath());
				}
			}
			return;
		}
		
		END:
		$output->writeln('Nothing picture has refactoring.');
	}
}