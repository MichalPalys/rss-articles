<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends Controller
{
    private $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    /**
     * @Route("/main", name="main")
     */
    public function displayAllArticle(Request $request)
    {
        $page = $request->query->get('page', 1);

        $qb = $this->articleRepository->findAllQueryBuilder();
        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta
            ->setMaxPerPage(10)
            ->setCurrentPage($page);

        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'my_pager' => $pagerfanta,
        ]);
    }

    /**
     * @Route("/article/{slug}/{id}", name="displayOneArticle")
     */
    public function displayOneArticles(string $slug, int $id)
    {
        $singleArticle = $this->articleRepository->findOneBy(['slug' => $slug], ['id' => $id]);

        return $this->render('main/article.html.twig', [
            'singleArticle' => $singleArticle,
        ]);
    }
}
