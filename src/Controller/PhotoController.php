<?php

namespace App\Controller;

use App\Repository\PhotoRepository;
use App\Service\DataPhotoService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
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


    protected function editAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_EDIT);

        $id = $this->request->query->get('id');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $entity = $easyadmin['item'];

        if ($this->request->isXmlHttpRequest() && $property = $this->request->query->get('property')) {
            $newValue = 'true' === mb_strtolower($this->request->query->get('newValue'));
            $fieldsMetadata = $this->entity['list']['fields'];

            if (!isset($fieldsMetadata[$property]) || 'toggle' !== $fieldsMetadata[$property]['dataType']) {
                throw new \RuntimeException(sprintf('The type of the "%s" property is not "toggle".', $property));
            }

            $this->updateEntityProperty($entity, $property, $newValue);

            // cast to integer instead of string to avoid sending empty responses for 'false'
            return new Response((int)$newValue);
        }

        $fields = $this->entity['edit']['fields'];

        $editForm = $this->executeDynamicMethod('create<EntityName>EditForm', [$entity, $fields]);
        $deleteForm = $this->createDeleteForm($this->entity['name'], $id);

        $flag = $this->request->files->get('photo');

        if ($flag['pathFile']) {
            $editForm->handleRequest($this->request);
            if ($editForm->isSubmitted() && $editForm->isValid()) {
                $this->dispatch(EasyAdminEvents::PRE_UPDATE, ['entity' => $entity]);

                $this->executeDynamicMethod('preUpdate<EntityName>Entity', [$entity, true]);
                $this->executeDynamicMethod('update<EntityName>Entity', [$entity]);

                $this->dispatch(EasyAdminEvents::POST_UPDATE, ['entity' => $entity]);

                return $this->redirectToReferrer();
            }
        }

        $this->dispatch(EasyAdminEvents::POST_EDIT);

        $parameters = [
            'form' => $editForm->createView(),
            'entity_fields' => $fields,
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        ];

        return $this->executeDynamicMethod('render<EntityName>Template', ['edit', $this->entity['templates']['edit'], $parameters]);
    }

    protected function persistEntity($entity)
    {
        $file = $entity->getPathFile();
        $url = $file->getPathname();
        $entity = $this->dataPhotoService->setDataPhoto($url);

        // updates the 'brochure' property to store the img file name
        // instead of its contents
        $entity->setName($file->getClientOriginalName());

        // moves the file to the directory where brochures are stored
        $file->move(
            $this->getParameter('photo_directory'),
            $entity->getPath()
        );

        parent::persistEntity($entity);
    }

    public function updateEntity($entity)
    {
        $file = $entity->getPathFile();
        $url = $file->getPathname();

        $fileContent = file_get_contents($url);
        $this->fileSystem->put($entity->getPath(), $fileContent);

        list($imgWidth, $imgHeight) = getimagesize($url);

        $entity->setName($file->getClientOriginalName());
        $entity->setWidth($imgWidth);
        $entity->setHeight($imgHeight);

        parent::updateEntity($entity);
    }

    protected function removeEntity($entity)
    {
        $this->fileSystem->delete($entity->getPath());

        parent::removeEntity($entity);
    }
}
