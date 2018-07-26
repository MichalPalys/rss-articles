<?php

namespace App\Controller;

use App\Service\FeedService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Photo;
use App\Repository\PhotoRepository;
use App\Form\EditPhotoEntityFormType;
use App\Service\DataPhotoService;

class PhotoController extends BaseAdminController
{
    private $photoRepository;
    private $dataPhotoService;

    public function __construct(
        PhotoRepository $photoRepository,
        DataPhotoService $dataPhotoService
    ) {
        $this->photoRepository = $photoRepository;
        $this->dataPhotoService = $dataPhotoService;
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
//            $tmpPhoto = FeedService::setDataPhoto($url);
            $tmpPhoto = $this->dataPhotoService->setDataPhoto($url);

            // updates the 'brochure' property to store the img file name
            // instead of its contents
            $photo->setName($file->getClientOriginalName());
            $photo->setPath($tmpPhoto->getPath());
            $photo->setWidth($tmpPhoto->getWidth());
            $photo->setHeight($tmpPhoto->getHeight());

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
}