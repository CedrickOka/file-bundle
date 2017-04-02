<?php
namespace Oka\FileBundle\Form\Type;

use Oka\FileBundle\Form\Model\FileFormType;

class ImageFormType extends FileFormType
{
	public function getBlockPrefix()
	{
		return 'oka_file_image_type';
	}
}