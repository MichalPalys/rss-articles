<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Photo;
use App\Repository\ArticleRepository;
use Cocur\Slugify\Slugify;
use League\Flysystem\Filesystem;
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

    private $slug;

    private $fileSystem;

    private $dataPhotoService;

    public function __construct(
        LoggerInterface $logger,
        ResponseCodeFromFeedService $respCodeFromFeed,
        ArticleRepository $articleRepository,
        FeedReader $feedReader,
        Filesystem $filesystem,
        array $rssLinkArray,
        Slugify $slug,
        DataPhotoService $dataPhotoService
    ) {
        $this->logger = $logger;
        $this->respCodeFromFeed = $respCodeFromFeed;
        $this->articleRepository = $articleRepository;
        $this->feedReader = $feedReader;
        $this->rssLinkArray = $rssLinkArray;
        $this->fileSystem = $filesystem;
        $this->slug = $slug;
        $this->dataPhotoService = $dataPhotoService;
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

            $photo = null;

            //logowanie dodania pojedyńczego artykułu
            $this->logger->info('Dodanie atrykułu z id: ' . $item->getId());

            $url = $item->getEnclosureUrl();

            if ($url) {
                $fileContent = file_get_contents($url);
//                $photo = $this->setDataPhoto($url);
                $photo = $this->dataPhotoService->setDataPhoto($url);
                $this->fileSystem->put($photo->getPath(), $fileContent);
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

//    public function setDataPhoto(string $url): Photo
//    {
//        $fileInfo = new \SplFileInfo($url);
//        $photo = new Photo();
//
//        list($imgWidth, $imgHeight, $imgType) = getimagesize($url);
//        $uniqueFilename = uniqid('', true);
//
//        $photo->setWidth($imgWidth);
//        $photo->setHeight($imgHeight);
//        $photo->setName($fileInfo->getFilename());
//        $photo->setPath($uniqueFilename . image_type_to_extension($imgType));
//
//        return $photo;
//    }
}
