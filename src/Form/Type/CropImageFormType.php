<?php
namespace Oka\FileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class CropImageFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('x0', TextType::class, ['required' => true])
				->add('y0', TextType::class, ['required' => true])
				->add('x1', TextType::class, ['required' => true])
				->add('y1', TextType::class, ['required' => true])
				->add('width', TextType::class, ['required' => true])
				->add('height', TextType::class, ['required' => true]);
	}
	
	public function getBlockPrefix()
	{
		return 'oka_file_crop_image_type';
	}
}
