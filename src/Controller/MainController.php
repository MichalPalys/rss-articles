<?php

namespace App\Controller;

use App\Entity\Article;
use App\Model\ArticleModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends Controller
{
    private $articleModel;

    public function __construct(ArticleModel $articleModel)
    {
        $this->articleModel = $articleModel;
    }

    /**
     * @Route("/main", name="main")
     */
    public function displayAllArticle(Request $request)
    {
        $page = $request->query->get('page', 1);

        $allArticles = $this->articleModel->displayAllArticles($page, 10);

        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'my_pager' => $allArticles,
        ]);
    }

    /**
     * @Route("/article/{slug}/{id}", name="displayOneArticle")
     */
    public function displayOneArticles(string $slug, int $id)
    {
        $singleArticle = $this->articleModel->displayOneArticles($slug, $id);

        if (!$singleArticle) {
            throw $this->createNotFoundException('Article not found!');
        }

        return $this->render('main/article.html.twig', [
            'singleArticle' => $singleArticle,
        ]);
    }
}
