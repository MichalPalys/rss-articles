<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Photo;
use App\Repository\ArticleRepository;
use Cocur\Slugify\Slugify;
use PicoFeed\Parser\Item;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class FeedService
{
    private $logger;

    private $respCodeFromFeed;

    private $articleRepository;

    private $feedReader;

    private $rssLinkArray;

    private $slug;

    public function __construct(
        LoggerInterface $logger,
        ResponseCodeFromFeedService $respCodeFromFeed,
        ArticleRepository $articleRepository,
        FeedReader $feedReader,
        array $rssLinkArray,
        Slugify $slug
    ) {
        $this->logger = $logger;
        $this->respCodeFromFeed = $respCodeFromFeed;
        $this->articleRepository = $articleRepository;
        $this->feedReader = $feedReader;
        $this->rssLinkArray = $rssLinkArray;
        $this->slug = $slug;
    }

    public function setFeedToDataBase()
    {
        // You can now use your logger
        $this->logger->info('Rozpoczęcie wykonywania skryptu.');

        foreach ($this->rssLinkArray as $rssLinkArrayValue) {
            try {
                $respCode = $this->respCodeFromFeed->getResponseCodeFromFeed($rssLinkArrayValue);

                if ($respCode !== 200) {
                    throw new Exception("HTTP Code = " . $respCode);
                }

                $this->logger->info('Strona odpowiada. Kod odpowiedzi serwera: ' . $respCode . ' dla URL ' . $rssLinkArrayValue . "\n");

                $feed = $this->feedReader->setFeedReader($rssLinkArrayValue);

                foreach ($feed->items as $item) {
                    $article = $this->getArticleToPersist($item);

                    if ($article) {
                        // tell Doctrine you want to (eventually) save the $article (no queries yet)
                        $this->articleRepository->persist($article);
                    }
                }

                // actually executes the queries (i.e. the INSERT query)
                $this->articleRepository->flush();
            } catch (\Exception $e) {
                $this->logger->info('Kod błędu odpowiedzi serwera: ' . $respCode . ' dla URL ' . $rssLinkArrayValue . "\n");
            }
        }

        // logowanie zakończenia skryptu
        $this->logger->info('Zakończenie wykonywania skryptu.');
    }

    public function getArticleToPersist(Item $item): ?Article
    {
        $article = null;
        $externalId = $item->getId();

        $existingArticle = $this->articleRepository->findOneBy(['externalId' => $externalId]);

        if (!$existingArticle) {

            $article = new Article();

            //logowanie dodania pojedyńczego artykułu
            $this->logger->info('Dodanie atrykułu z id: ' . $item->getId());

            $photo = new Photo();

            $url = $item->getEnclosureUrl();
            $adapter = new Local(__DIR__.'/../../public/photo');
            $localAdapter = new Filesystem($adapter);


            If (isset($url)) {
                $fileContent = file_get_contents($url);
                $filePath = $this->articleProfilePicture($item);
                $localAdapter->put($filePath,  $fileContent);
                $photo->setName($filePath);
                $photo->setPath('/public/photo/');

                // Pobieranie szerokości i wysokości obrazu
                $fileInfo = getimagesize($item->getEnclosureUrl());
                $photo->setHeight($fileInfo[0]);
                $photo->setWidth($fileInfo[1]);

                // dla celów testowych
                echo image_type_to_extension($fileInfo[2]) . PHP_EOL;
                echo $fileInfo['mime']. PHP_EOL;
            }
            else {
                $photo = null;
            }

            $article->setExternalId($item->getId());
            $article->setTitle($item->getTitle());
            $article->setPubDate($item->getPublishedDate());
            $article->setInsertDate($item->getUpdatedDate());
            $article->setContent($item->getContent());
            $article->setPhoto($photo);
            $article->setSlug($this->slug->slugify($item->getTitle()));
        }

        return $article;
    }

    public function articleProfilePicture($feedItem): string
    {
        $filePath = strrchr($feedItem->getEnclosureUrl(), "/");

        return $filePath;
    }
}
