<?php
namespace Oka\FileBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Oka\FileBundle\Utils\FileUtil;

/**
 * 
 * @author cedrick
 * 
 */
class UpgradeImageCommand extends ContainerAwareCommand
{
	/**
	 * Configure
	 * 
	 * @see \Symfony\Component\Console\Command\Command::configure()
	 */
	protected function configure()
	{
		$this->setName('okafile:upgrade:image')
			 ->setDescription('Upgrade images')
			 ->setDefinition([
			 		new InputArgument('class', InputArgument::OPTIONAL, 'Image class name', null)
			 ])
			 ->setHelp('Upgrade images.');
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
		
		if ($class = $input->getArgument('class')) {
			$imageManager->setClass($class);
		}
		
		$objectManager = $imageManager->getObjectManager();
		$detectDominantColor = $container->getParameter('oka_file.image.uploaded.detect_dominant_color');
		
		$offset = 0;
		$path = null;
		$thumbnailsBuilded = true;
		$output->writeln('Upgrading images...');
		
		while ($images = $imageManager->findFilesBy([], [], 100, $offset)) {
			/** @var \Oka\FileBundle\Model\Image $image */
			foreach ($images as $image) {
				$image->setDominantColor($uploadedImageManager->findImageDominantColor($image->getRealPath(), $detectDominantColor['method'], $detectDominantColor['options']));
				$image->setSize(filesize($image->getRealPath()));
				
				if (OutputInterface::VERBOSITY_NORMAL === $output->getVerbosity()) {
					$output->writeln(sprintf(
							'[<comment>%s</comment>] Image with path <info>%s</info>, with size <comment>%s Mo</comment>, to as dominant color <comment>#%s</comment>.',
							date('H:i:s'),
							$image->getRealPath(),
							round($image->getSize() / 1048576, 2),
							$image->getDominantColor()
					));
				}
				
				if ($thumbnailsBuilded === true) {
					$thumbnailsBuilded = $uploadedImageManager->buildThumbnails($image);
					
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
				
				if ($path === null) {
					$path = $image->getPath();
				}
			}
			
			$objectManager->flush();
			$offset += 100;
		}
		
		if ($path !== null) {
			$user = FileUtil::getSystemOwner();
			FileUtil::getFs()->chown($path, $user, true);
			FileUtil::getFs()->chgrp($path, $user, true);
		}
	}
}
