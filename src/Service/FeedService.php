<?php

namespace App\Service;

use App\Entity\Article;
use App\Repository\UserRepository;
use App\Entity\User;
//use FOS\UserBundle\Model\User as BaseUser;
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

    private $userRepository;

    private $existingAdmin;


    public function __construct(
        LoggerInterface $logger,
        ResponseCodeFromFeedService $respCodeFromFeed,
        ArticleRepository $articleRepository,
        FeedReader $feedReader,
        Filesystem $filesystem,
        array $rssLinkArray,
        Slugify $slug,
        DataPhotoService $dataPhotoService,
        UserRepository $userRepository
    ) {
        $this->logger = $logger;
        $this->respCodeFromFeed = $respCodeFromFeed;
        $this->articleRepository = $articleRepository;
        $this->feedReader = $feedReader;
        $this->rssLinkArray = $rssLinkArray;
        $this->fileSystem = $filesystem;
        $this->slug = $slug;
        $this->dataPhotoService = $dataPhotoService;
        $this->userRepository = $userRepository;
    }

    public function setFeedToDataBase()
    {
        // You can now use your logger
        $this->logger->info('Rozpoczęcie wykonywania skryptu.');

        $this->existingAdmin = $this->getExistingAdmin();

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

    public function getExistingAdmin(string $username = 'admin')
    {
        $admin = $this->userRepository->findOneBy(['username' => $username]);
        if (!$admin) {
            throw new \DomainException(sprintf('Admin %s does not exist! Maybe You should create one.', $username));
        }

        return $admin;
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


            $article->setAuthor($this->existingAdmin);
        }

        return $article;
    }
}
