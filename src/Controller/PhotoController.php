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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Util\LegacyFormHelper;

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

    protected function createEntityFormBuilder($entity, $view)
    {
        $formOptions = $this->executeDynamicMethod('get<EntityName>EntityFormOptions', array($entity, $view));

        $formBuilder = $this->get('form.factory')->createNamedBuilder(mb_strtolower($this->entity['name']), LegacyFormHelper::getType('easyadmin'), $entity, $formOptions);
        $formBuilder->add('pathFile', FileType::class, array('required' => false, 'label' => 'article.photo', 'attr' => ['novalidate'=> 'novalidate']));

        return $formBuilder;
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