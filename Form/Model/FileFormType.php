<?php
namespace Oka\FileBundle\Form\Model;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class FileFormType extends AbstractType
{
	/**
	 * @var string $class
	 */
	protected $class;
	
	public function __construct($class)
	{
		$this->class = $class;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('name', TextType::class, [
						'required'	=> false,
						'attr'		=> ['placeholder' => 'Entrez le nom du fichier'],
					])
				->add('uploadedFile', FileType::class, [
						'required'	=> isset($options['required']) ? $options['required'] : true,
					]);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
				'data_class' 		=> $this->class,
				'validation_groups' => ['Upload'],
		]);
	}
}