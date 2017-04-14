<?php
namespace Oka\FileBundle\Command;

use Oka\FileBundle\Utils\FileUtil;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 *
 * @author cedrick
 *
 */
class ImageThumbnailFactoryCommand extends ContainerAwareCommand
{
	/**
	 * Configure
	 *
	 * @see \Symfony\Component\Console\Command\Command::configure()
	 */
	protected function configure()
	{
		$this->setName('okafile:image:thumbnail:build')
			 ->setDescription('Build thumbnails of images')
			 ->setDefinition([
			 		new InputArgument('imageClass', InputArgument::OPTIONAL, 'Image class name', null)
			 ])
			 ->setHelp('Cette commande permet de minuaturiser les images dans les tailles définies dans le factory.');
	}
	
	/**
	 * Execute
	 *
	 * @see \Symfony\Component\Console\Command\Command::execute()
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
		/** @var \Symfony\Component\DependencyInjection\Container $container */
		$container = $this->getContainer();
		/** @var \Oka\FileBundle\Model\FileManagerInterface $imageManager */
		$imageManager = $container->get('oka_file.image_manager');
		/** @var \Oka\FileBundle\Service\UploadedImageManager $uploadedImageManager */
		$uploadedImageManager = $container->get('oka_file.uploaded_image.manager');
		
		if ($class = $input->getArgument('imageClass')) {
			$imageManager->setClass($class);
		}
		
		if ($images = $imageManager->findFiles()) {
			$output->writeln('Building of thumbnails of images...');
			
			/** @var \Oka\FileBundle\Model\FileInterface $image */
			foreach ($images as $image) {
				$thumbnailsBuilded = $uploadedImageManager->buildThumbnails($image);
				
				if ($thumbnailsBuilded === false) {
					$output->writeln('No image to build.');
					break;
				}
				
				if (!empty($thumbnailsBuilded)) {
					if (OutputInterface::VERBOSITY_NORMAL === $output->getVerbosity()) {
						$output->writeln(sprintf(
								'[<comment>%s</comment>] Image with path <info>%s</info>, <comment>%s</comment> thumbnails were created.',
								date('H:i:s'),
								$image->getRealPath(),
								count($thumbnailsBuilded)
						));
					}
				}
			}
			
			if (isset($image)) {
				$user = FileUtil::getSystemOwner();
				FileUtil::getFs()->chown($image->getPath(), $user, true);
				FileUtil::getFs()->chgrp($image->getPath(), $user, true);
			}
		}
	}
}