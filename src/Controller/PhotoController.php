<?php

namespace App\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Photo;
use App\Repository\PhotoRepository;
use App\Form\EditPhotoEntityFormType;
use App\Service\DataPhotoService;
use League\Flysystem\Filesystem;

class PhotoController extends BaseAdminController
{
    private $photoRepository;
    private $dataPhotoService;
    private $fileSystem;

    public function __construct(
        PhotoRepository $photoRepository,
        DataPhotoService $dataPhotoService,
        Filesystem $filesystem
    ) {
        $this->photoRepository = $photoRepository;
        $this->dataPhotoService = $dataPhotoService;
        $this->fileSystem = $filesystem;
    }

    public function newAction()
    {
        $photo = new Photo();

        $fields = $this->entity['new']['fields'];

        $form = $this->createForm(EditPhotoEntityFormType::class, $photo);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $file stores the uploaded img file
            $file = $photo->getPathFile();
            $url = $file->getPathname();
            $photo = $this->dataPhotoService->setDataPhoto($url);

            // updates the 'brochure' property to store the img file name
            // instead of its contents
            $photo->setName($file->getClientOriginalName());

            // moves the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('photo_directory'),
                $photo->getPath()
            );

            // ... persist the $product variable or any other work
            $this->photoRepository->persist($photo);
            $this->photoRepository->flush();

            return $this->redirectToReferrer();
        }

        $parameters = array(
            'form' => $form->createView(),
            'entity_fields' => $fields,
            'entity' => $photo,
        );

        return $this->render('@EasyAdmin/default/new.html.twig', $parameters);
    }

    public function editAction()
    {
        $id = $this->request->query->get('id');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $entity = $easyadmin['item'];

        $fields = $this->entity['new']['fields'];

        $editForm = $this->createForm(EditPhotoEntityFormType::class, $entity);
        $deleteForm = $this->createDeleteForm($this->entity['name'], $id);

        $flag =$this->request->files->get('edit_photo_entity_form');

        if ($flag['pathFile']) {
            $editForm->handleRequest($this->request);

            if ($editForm->isSubmitted() && $editForm->isValid()) {
                $this->executeDynamicMethod('preUpdate<EntityName>Entity', array($entity, true));

                $file = $entity->getPathFile();
                $url = $file->getPathname();

                $fileContent = file_get_contents($url);
                $this->fileSystem->put($entity->getPath(), $fileContent);

                $photo = $this->dataPhotoService->setDataPhoto($url);

                $entity->setName($file->getClientOriginalName());
                $entity->setWidth($photo->getWidth());
                $entity->setHeight($photo->getHeight());

                $this->executeDynamicMethod('update<EntityName>Entity', array($entity));

                return $this->redirectToReferrer();
            }
        }
        $parameters = array(
            'form' => $editForm->createView(),
            'entity_fields' => $fields,
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );

        return $this->executeDynamicMethod('render<EntityName>Template', array('edit', $this->entity['templates']['edit'], $parameters));
    }

    protected function removeEntity($entity)
    {
        $this->fileSystem->delete($entity->getPath());

        parent::removeEntity($entity);
    }

}