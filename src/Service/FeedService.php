<?php

namespace App\Service;

use App\Entity\Article;
use App\Repository\ArticleRepository;
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
        $rssLinkArray
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
                    $this->getArticleToPersist($item);
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

    public function getArticleToPersist($item)
    {
        $externalId = $item->getId();
        $itemArticleFlag = $this->articleRepository->findOneBy(['externalId' => $externalId]);

        if (!$itemArticleFlag) {
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
    }
}
