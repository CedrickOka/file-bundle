<?php
namespace Oka\FileBundle\Event;

use Oka\FileBundle\Model\FileInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class FormEvent extends FileEvent
{
	/**
	 * @var FormInterface $form
	 */
    protected $form;
    
    /**
     * @param FormInterface $form
     * @param FileInterface $file
     * @param Request $request
     */
    public function __construct(FormInterface $form, FileInterface $file, Request $request)
    {
    	parent::__construct($file, $request);
    	
        $this->form = $form;
    }

    /**
     * @return FormInterface
     */
    public function getForm() {
        return $this->form;
    }
}
