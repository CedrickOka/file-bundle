<?php
namespace Oka\FileBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * 
 * @author cedrick
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
				new InputArgument('class', InputArgument::OPTIONAL, 'Image class name', null)
		])
		->setHelp(<<<EOF
Allows to find dominant color of images.
EOF
				);
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
		$objectManager = $imageManager->getObjectManager();
		
		if (!$dominantColorMethod = $input->getOption('method')) {
			$detectDominantColor = $container->getParameter('oka_file.image.uploaded.detect_dominant_color');
			$dominantColorMethod = $detectDominantColor['method'];
		}
		
		if ($class = $input->getArgument('class')) {
			$imageManager->setClass($class);
		}
		
		$output->writeln(sprintf('Finding dominant color of images with method <comment>%s</comment>...', $dominantColorMethod));
		
		while ($images = $imageManager->findFilesBy(['dominantColor' => null], [], 100)) {
			/** @var \Oka\FileBundle\Model\ImageInterface $image */
			foreach ($images as $image) {
				$colorRGB = $uploadedImageManager->findImageDominantColor($image->getRealPath(), $dominantColorMethod);
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
		}
	}
}
