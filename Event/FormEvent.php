<?php
namespace Oka\FileBundle\Event;

use Oka\FileBundle\Model\FileInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * @author cedrick
 * 
 */
class FormEvent extends FileEvent
{
    protected $form;
    protected $response;
    
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

    public function setResponse(Response $response) {
        $this->response = $response;
        return $this;
    }
    
    /**
     * @return Response|null
     */
    public function getResponse() {
        return $this->response;
    }
}