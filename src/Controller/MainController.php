<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Model\ArticleModel;
use App\Repository\CommentRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

class MainController extends BaseAdminController
{
    private $articleModel;

    private $commentRepository;

    private $translator;

    public function __construct(ArticleModel $articleModel, CommentRepository $commentRepository, TranslatorInterface $translator)
    {
        $this->articleModel = $articleModel;
        $this->commentRepository = $commentRepository;
        $this->translator = $translator;
    }

    /**
     * @Route("/main", name="main")
     */
    public function displayAllArticle(Request $request)
    {
        $page = $request->query->get('page', '1');

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

        $articleId = $singleArticle->getId();

        $articleComments = $this->commentRepository->findAllByArticle($articleId);

        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setCreateDate(date_create('now'));

            $this->commentRepository->persist($comment);
            $this->commentRepository->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('saved_changes_message')
            );

            return $this->redirectToRoute('main');
        }

        return $this->render('main/article.html.twig', [
            'singleArticle' => $singleArticle,
            'commentForm' => $commentForm->createView(),
            'commentsList' => $articleComments,
        ]);
    }
}
