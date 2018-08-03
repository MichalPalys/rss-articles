<?php

namespace App\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use App\Repository\ArticleRepository;
use Cocur\Slugify\Slugify;

class ArticleController extends BaseAdminController
{
    private $articleRepository;
    private $slug;

    public function __construct(
        ArticleRepository $articleRepository,
        Slugify $slug
    ) {
        $this->articleRepository = $articleRepository;
        $this->slug = $slug;
    }

    protected function persistEntity($entity)
    {
        $entity->setSlug($this->slug->slugify($entity->getTitle()));

        parent::persistEntity($entity);
    }
}