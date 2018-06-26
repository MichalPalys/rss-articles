<?php
/**
 * Created by PhpStorm.
 * User: michalpalys
 * Date: 20.06.18
 * Time: 13:45
 */
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

    private $rssLinkArray = [
        'http://www.rmf24.pl/sport/feed',
        'http://www.komputerswiat.pl/rss-feeds/komputer-swiat-feed.aspx',
        'http://xmoon.pl/rss/rss.xml',
    ];

    public function __construct(
        LoggerInterface $logger,
        ResponseCodeFromFeedService $respCodeFromFeed,
        ArticleRepository $articleRepository,
        FeedReader $feedReader
    ) {
        $this->logger = $logger;
        $this->respCodeFromFeed = $respCodeFromFeed;
        $this->articleRepository = $articleRepository;
        $this->feedReader = $feedReader;
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

                foreach ($feed->items as $key => $val) {
                    $externalId = $feed->items[$key]->getId();
                    $itemArticleFlag = $this->articleRepository->findOneBy(['externalId' => $externalId]);

                    if (!$itemArticleFlag) {
                        $article = new Article();

                        //logowanie dodania pojedyńczego artykułu
                        $this->logger->info('Dodanie atrykułu z id: ' . $feed->items[$key]->getId());

                        $article->setExternalId($feed->items[$key]->getId());
                        $article->setTitle($feed->items[$key]->getTitle());
                        $article->setPubDate($feed->items[$key]->getPublishedDate());
                        $article->setInsertDate($feed->items[$key]->getUpdatedDate());
                        $article->setContent($feed->items[$key]->getContent());

                        // tell Doctrine you want to (eventually) save the $article (no queries yet)
                        $this->articleRepository->save($article);
                    }
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
}
