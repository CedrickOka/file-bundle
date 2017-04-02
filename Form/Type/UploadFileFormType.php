<?php
namespace Oka\FileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadFileFormType extends AbstractType
{
	/**
	 * @var string $class
	 */
	private $class;
	
	public function __construct($class)
	{
		$this->class = $class;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('uploadedFile', FileType::class, [
				'required' => isset($options['required']) ? $options['required'] : true
		]);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' 		=> $this->class,
			'validation_groups' => ['Upload']
		]);
	}
	
	public function getBlockPrefix()
	{
		return 'oka_file_upload_file_type';
	}
}