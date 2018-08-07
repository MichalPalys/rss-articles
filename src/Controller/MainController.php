<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Model\ArticleModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use App\Repository\CommentRepository;
use App\Entity\User;
use FOS\UserBundle\Model\User as BaseUser;

class MainController extends BaseAdminController
{
    private $articleModel;

    private $commentRepository;

    public function __construct(ArticleModel $articleModel, CommentRepository $commentRepository)
    {
        $this->articleModel = $articleModel;
        $this->commentRepository = $commentRepository;
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
    public function displayOneArticles(string $slug, int $id, Request $request)
    {
        $singleArticle = $this->articleModel->displayOneArticles($slug, $id);

        if (!$singleArticle) {
            throw $this->createNotFoundException('Article not found!');
        }

        $comment = new Comment();
        $comment->setArticle($singleArticle);
        $comment->setMaker($this->getUser());

        $commentForm = $this->createForm(CommentType::class, $comment);

        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {

            $comment->setCreateDate(date_create('now'));

            $this->commentRepository->persist($comment);
            $this->commentRepository->flush();

            $page = $request->query->get('page', 1);

            $allArticles = $this->articleModel->displayAllArticles($page, 10);

            return $this->render('main/index.html.twig', [
                'controller_name' => 'MainController',
                'my_pager' => $allArticles,
            ]);
        }

        return $this->render('main/article.html.twig', [
            'singleArticle' => $singleArticle, 'commentForm' => $commentForm->createView()
        ]);
    }
}
