<?php
namespace Oka\FileBundle\Form\Type;

/**
 *
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class ImageFormType extends FileFormType
{
	public function getBlockPrefix()
	{
		return 'oka_file_image';
	}
}
