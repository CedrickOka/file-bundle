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
		
		if ($class = $input->getArgument('class')) {
			$imageManager->setClass($class);
		}
		
		while ($images = $imageManager->findFilesBy(['dominantColor' => null], [], 100)) {
			$output->writeln('Finding dominant color of images...');
			
			/** @var \Oka\FileBundle\Model\ImageInterface $image */
			foreach ($images as $image) {
				$colorRGB = $uploadedImageManager->findImageDominantColor($image->getRealPath());
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
