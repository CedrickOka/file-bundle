<?php
namespace Oka\FileBundle\Controller;

use Oka\FileBundle\Form\Type\CropImageFormType;
use Oka\FileBundle\Form\Type\ImageFormType;
use Oka\FileBundle\Form\Type\UploadFileFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 *
 * @author  Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class ImageController extends Controller
{
	/**
	 * List Image
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listAction(Request $request)
	{
		$result = $this->get('oka_pagination.manager')->paginate($this->getParameter('oka_file.image.default_class'), $request, [], ['createdAt' => 'DESC']);
		
		return $this->render('OkaFileBundle:Image:list.html.twig', [
				'entities' => $result->getItems()
		]);
	}
	
	/**
	 * Show image
	 * 
	 * @param Request $request
	 * @param mixed $id
	 * @throws NotFoundHttpException
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function showAction(Request $request, $id)
	{
		/** @var \Oka\FileBundle\Model\FileManagerInterface $fileManager */
		$fileManager = $this->get('oka_file.image_manager');
		
		/** @var \Oka\FileBundle\Model\ImageInterface $entity */
		if ($entity = $fileManager->findFile($id)) {
			return $this->render('OkaFileBundle:Image:show.html.twig', ['entity' => $entity]);
		}
		
		throw new NotFoundHttpException('Page introuvable!');
	}
	
	/**
	 * Create a new image
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function createAction(Request $request)
	{
		$router = $this->get('router');
		$formFactory = $this->get('form.factory');
		/** @var \Oka\FileBundle\Model\FileManagerInterface $fileManager */
		$fileManager = $this->get('oka_file.image_manager');
		
		$image = $fileManager->createFile();
		$form = $formFactory->create(ImageFormType::class, $image, [
				'action' => $router->generate('oka_file_image_create', [], UrlGeneratorInterface::ABSOLUTE_PATH),
				'method' => 'POST'
		]);
		$form->add('save', SubmitType::class, ['label' => 'Enregistrer']);

		if (true === $request->isMethod('POST')) {
			$form->handleRequest($request);
			
			if ($form->isValid()) {
				$fileManager->updateFile($image);
				$url = $router->generate('oka_file_image_show', ['id' => $image->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
				
				return new RedirectResponse($url, 302);
			}
		}
		
		return $this->render('OkaFileBundle:Image:create.html.twig', [
				'form' => $form->createView()
		]);  	 
	}
	
	public function updateAction(Request $request, $id)
	{
		/** @var \Oka\FileBundle\Model\FileManagerInterface $fileManager */
		$fileManager = $this->get('oka_file.image_manager');
		
		/** @var \Oka\FileBundle\Model\ImageInterface $entity */
		if ($entity = $fileManager->findFile($id)) {
			$router = $this->get('router');
			$formFactory = $this->get('form.factory');
			
			$form = $formFactory->create(ImageFormType::class, $entity, [
					'action' => $router->generate('oka_file_image_update', ['id' => $entity->getId()], UrlGeneratorInterface::ABSOLUTE_PATH),
					'method' => 'POST',
					'required' => false
			]);
			$form->add('save', SubmitType::class, ['label' => 'Modifier']);
			
			if (true === $request->isMethod('POST')) {
				$form->handleRequest($request);
				
				if ($form->isValid()) {
					$fileManager->updateFile($entity);
					$url = $router->generate('oka_file_image_show', array('id' => $entity->getId()), UrlGeneratorInterface::ABSOLUTE_PATH);
					
					return new RedirectResponse($url, 302);
	   			}
			}
			return $this->render('OkaFileBundle:Image:update.html.twig', [
					'form' => $form->createView(), 
					'entity' => $entity
			]);
		}
		throw new NotFoundHttpException('Page introuvable!');
	}
	
	public function deleteAction($id)
	{
		/** @var \Oka\FileBundle\Model\FileManagerInterface $fileManager */
		$fileManager = $this->get('oka_file.image_manager');
		
		if ($entity = $fileManager->findFile($id)) {
			$fileManager->deleteFile($entity);
			$url = $this->get('router')->generate('oka_file_image_list', [], UrlGeneratorInterface::ABSOLUTE_PATH);
			
			return new RedirectResponse($url, 302);
		}
		throw new NotFoundHttpException('Page introuvable');
	}
	
	/**
	 * Upload image
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function uploadAction(Request $request)
	{
		/** @var \Oka\FileBundle\Model\FileManagerInterface $fileManager */
		$fileManager = $this->get('oka_file.image_manager');
		/** @var $formFactory Symfony\Component\Form\FormInterface */
		$formFactory = $this->get('form.factory');
		/** @var $router Symfony\Component\Routing\RouterInterface */
		$router = $this->get('router');
		
		/** @var \Oka\FileBundle\Model\ImageInterface $entity */
		$entity = $fileManager->createFile();
		$form = $formFactory->create(new UploadFileFormType($fileManager->getClass()), $entity, [
				'action' => $router->generate('oka_file_image_upload', [], UrlGeneratorInterface::ABSOLUTE_PATH),
				'method' => 'POST'
		]);
		$form->add('upload', SubmitType::class, ['label' => 'Télécharger']);
		
		if (true === $request->isMethod('POST')) {
			$form->handleRequest($request);
			
			if ($form->isValid()) {
				$fileManager->updateFile($entity);
				$url = $router->generate('oka_file_image_show', ['id' => $entity->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
				
				return new RedirectResponse($url, 302);
			}
		}
		return $this->render('OkaFileBundle:Image:upload.html.twig', ['form' => $form->createView()]);
	}
	
	/**
	 * Delete image version
	 * 
	 * @param mixed $id
	 * @param integer $width
	 * @param integer $height
	 * @throws NotFoundHttpException
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function deleteSizeAction($id, $width, $height)
	{
		/** @var \Oka\FileBundle\Model\FileManagerInterface $fileManager */
		$fileManager = $this->get('oka_file.image_manager');
		
		/** @var \Oka\FileBundle\Model\ImageInterface $entity */
		if ($entity = $fileManager->findFile($id)) {
			$entity->removeFileFor($width, $height);
			$url = $this->get('router')->generate('oka_file_image_show', ['id' => $entity->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
			
			return new RedirectResponse($url, 302);
		}
		throw new NotFoundHttpException('Page introuvable');
	}
	
	/**
	 * Crop an image
	 * 
	 * @param Request $request
	 * @param mixed $id
	 * @throws NotFoundHttpException
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response|NULL|\Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function cropAction(Request $request, $id)
	{
		/** @var \Oka\FileBundle\Model\FileManagerInterface $fileManager */
		$fileManager = $this->get('oka_file.image_manager');
		
		/** @var \Oka\FileBundle\Model\ImageManipulatorInterface $entity */
		if ($entity = $fileManager->findFile($id)) {
			/** @var $formFactory Symfony\Component\Form\FormInterface */
			$formFactory = $this->get('form.factory');
			/** @var $router Symfony\Component\Routing\RouterInterface */
			$router = $this->get('router');
			
			$form = $formFactory->create(CropImageFormType::class, null, [
					'action' => $router->generate('oka_file_image_crop', ['id' => $entity->getId()], UrlGeneratorInterface::ABSOLUTE_PATH),
					'method' => 'POST'
			]);
			$form->add('save', SubmitType::class, ['label' => 'Enregistrer']);
			
			if (true === $request->isMethod('POST')) {
				$form->handleRequest($request);
				
				if ($form->isValid()) {
					$data = $form->getData();
					$entity->crop($data['x0'], $data['y0'], $data['x1'], $data['y1']);					
					$url = $router->generate('oka_file_image_show', ['id' => $entity->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
					
					return new RedirectResponse($url, 302);
				}
			}
			return $this->get('templating')->renderResponse('OkaFileBundle:Image:crop.html.twig', [
					'entity' => $entity, 
					'form' => $form->createView()
			]);			
		}
		throw new NotFoundHttpException('Page introuvable');
	}
	
	public function snapshotAction(Request $request)
	{
		$str = file_get_contents('php://input');
		$path = sys_get_temp_dir() . '/upload.jpg';
		file_put_contents($path, pack('H*', $str));
		
		return new  BinaryFileResponse($str);
	}
}
