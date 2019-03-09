<?php
namespace Oka\FileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class UploadFileType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('uploadedFile', FileType::class, [
				'required' => true === isset($options['required']) ? $options['required'] : true
		]);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(['validation_groups' => ['Upload']]);
	}
	
	public function getBlockPrefix()
	{
		return 'oka_file_uploaded_file';
	}
}
