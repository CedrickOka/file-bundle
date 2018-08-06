<?php
namespace Oka\FileBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class ImageDominantColorCommand extends ContainerAwareCommand
{
	/**
	 * Configure
	 * 
	 * @see \Symfony\Component\Console\Command\Command::configure()
	 */
	protected function configure()
	{
		$this->setName('okafile:image:find:dominant-color')
			 ->setDescription('Find dominant color of images')
			 ->setDefinition([
			 		new InputOption('method', 'm', InputOption::VALUE_OPTIONAL, 'Find dominant color of image with this method.', null),
			 		new InputOption('findAll', 'f', InputOption::VALUE_NONE, 'Find dominant color of all images.'),
			 		new InputArgument('class', InputArgument::OPTIONAL, 'Image class name', null)
			 ])
			 ->setHelp('Allows to find dominant color of images.');
	}
	
	/**
	 * Execute
	 * 
	 * @see \Symfony\Component\Console\Command\Command::execute()
	 */
	public function execute(InputInterface $input, OutputInterface $output) {
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
		
		if (!$dominantColorMethod = $input->getOption('method')) {
			$dominantColorMethod = $detectDominantColor['method'];
		}
		
		$offset = 0;
		$criteria = ($findAll = $input->getOption('findAll')) === true ? [] : ['dominantColor' => null];
		$output->writeln(sprintf('Finding dominant color of images with method <comment>%s</comment>...', $dominantColorMethod));
		
		while ($images = $imageManager->findFilesBy($criteria, ['createdAt' => 'DESC'], 100, $offset)) {
			/** @var \Oka\FileBundle\Model\ImageInterface $image */
			foreach ($images as $image) {
				$colorRGB = $uploadedImageManager->findImageDominantColor($image->getRealPath(), $dominantColorMethod, $detectDominantColor['options']);
				$image->setDominantColor($colorRGB);
				
				if (OutputInterface::VERBOSITY_NORMAL === $output->getVerbosity()) {
					$output->writeln(sprintf(
							'[<comment>%s</comment>] Image with path <info>%s</info>, to as dominant color <comment>#%s</comment>.',
							date('H:i:s'),
							$image->getRealPath(),
							$colorRGB
					));
				}
			}
			
			$objectManager->flush();
			
			if ($findAll === true) {
				$offset += 100;
			}
		}
	}
}
