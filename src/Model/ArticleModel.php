<?php

namespace App\Model;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class ArticleModel
{
    private $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }


    /**
     * @param int $page
     * @param int $maxPerPage
     * @return Pagerfanta
     */
    public function displayAllArticles(int $page, int $maxPerPage): Pagerfanta
    {
        $qb = $this->articleRepository->findAllQueryBuilder();
        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);

        return $pagerfanta
            ->setMaxPerPage($maxPerPage)
            ->setCurrentPage($page);
    }

    /**
     * @param string $slug
     * @param int $id
     * @return Article
     */
    public function displayOneArticles(string $slug, int $id): Article
    {
        $singleArticle = $this->articleRepository->findOneBy(['slug' => $slug, 'id' => $id]);

        return $singleArticle;
    }
}