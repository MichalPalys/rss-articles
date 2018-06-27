<?php

namespace App\Service;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use PicoFeed\Parser\Item;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

class FeedService
{
    private $logger;

    private $respCodeFromFeed;

    private $articleRepository;

    private $feedReader;

    private $rssLinkArray;

    public function __construct(
        LoggerInterface $logger,
        ResponseCodeFromFeedService $respCodeFromFeed,
        ArticleRepository $articleRepository,
        FeedReader $feedReader,
        array $rssLinkArray
    ) {
        $this->logger = $logger;
        $this->respCodeFromFeed = $respCodeFromFeed;
        $this->articleRepository = $articleRepository;
        $this->feedReader = $feedReader;
        $this->rssLinkArray = $rssLinkArray;
    }

    public function setFeedToDataBase()
    {
        // You can now use your logger
        $this->logger->info('Rozpoczęcie wykonywania skryptu.');

        foreach ($this->rssLinkArray as $rssLinkArrayValue) {
            try {
                $respCode = $this->respCodeFromFeed->getResponseCodeFromFeed($rssLinkArrayValue);

                if ($respCode != 200) {
                    throw new Exception("HTTP Code = " . $respCode);
                }

                $this->logger->info('Strona odpowiada. Kod odpowiedzi serwera: ' . $respCode . ' dla URL ' . $rssLinkArrayValue . "\n");

                $feed = $this->feedReader->setFeedReader($rssLinkArrayValue);

                foreach ($feed->items as $item) {
                    $article = $this->getArticleToPersist($item, Article);
                }

                // actually executes the queries (i.e. the INSERT query)
                $this->articleRepository->execQuery();
            } catch (\Exception $e) {
                $this->logger->info('Kod błędu odpowiedzi serwera: ' . $respCode . ' dla URL ' . $rssLinkArrayValue . "\n");
            }
        }

        // logowanie zakończenia skryptu
        $this->logger->info('Zakończenie wykonywania skryptu.');
    }

    public function getArticleToPersist(Item $item, Article $article): Article
    {
        $externalId = $item->getId();
        $existingArticle = $this->articleRepository->findOneBy(['externalId' => $externalId]);

        if (!$existingArticle) {
            $article = new Article();

            //logowanie dodania pojedyńczego artykułu
            $this->logger->info('Dodanie atrykułu z id: ' . $item->getId());

            $article->setExternalId($item->getId());
            $article->setTitle($item->getTitle());
            $article->setPubDate($item->getPublishedDate());
            $article->setInsertDate($item->getUpdatedDate());
            $article->setContent($item->getContent());

            // tell Doctrine you want to (eventually) save the $article (no queries yet)
            $this->articleRepository->save($article);
        }
        //bez poniższego zapisu rzuca błąd
        //Return value of App\Service\FeedService::getArticleToPersist() must be an instance of App\Entity\Article, null returned
        //$article = $article;

        return $article;
    }
}
