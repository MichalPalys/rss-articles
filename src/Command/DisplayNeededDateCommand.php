<?php
/**
 * Created by PhpStorm.
 * User: michalpalys
 * Date: 14.06.18
 * Time: 08:54
 */

/**
 * Created by PhpStorm.
 * User: michalpalys
 * Date: 13.06.18
 * Time: 13:39
 */
// src/Command/DisplayEntireRssFileCommand.php

namespace App\Command;

//use App\Entity\Article;
//use App\Repository\ArticleRepository;
//use App\Service\ResponseCodeFromFeedService;
//use PicoFeed\Reader\Reader;
//use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use App\Service\FeedService;
//use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisplayNeededDateCommand extends ContainerAwareCommand
{
//    private $logger;
//
//    private $respCodeFromFeed;
//
//    private $articleRepository;
//
//    public function __construct(LoggerInterface $logger, ResponseCodeFromFeedService $respCodeFromFeed, ArticleRepository $articleRepository)
//    {
//        parent::__construct();
//        $this->logger = $logger;
//        $this->respCodeFromFeed = $respCodeFromFeed;
//        $this->articleRepository = $articleRepository;
//    }

    private $feedService;

    public function __construct(FeedService $feedService)
    {
        parent::__construct();
        $this->feedService = $feedService;
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('rss:get-needed')

            // the short description shown while running "php bin/console list"
            ->setDescription('Show what we need.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to display all needed data for us.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $output->writeln($this->printRss());
        $output->writeln($this->feedService->setFeedToDataBase());
    }

//    public function printRss()
//    {
//        // You can now use your logger
//        $this->logger->info('Rozpoczęcie wykonywania skryptu.');
//
//        $rssLinkArray = [
//            'http://www.rmf24.pl/sport/feed',
//            'http://www.komputerswiat.pl/rss-feeds/komputer-swiat-feed.aspx',
//            'http://xmoon.pl/rss/rss.xml',
//        ];
//
//        foreach ($rssLinkArray as $rssLinkArrayValue) {
//            try {
//                $respCode = $this->respCodeFromFeed->getResponseCodeFromFeed($rssLinkArrayValue);
//
//                if ($respCode != 200) {
//                    throw new Exception("HTTP Code = " . $respCode);
//                }
//
//                $this->logger->info('Strona odpowiada. Kod odpowiedzi serwera: ' . $respCode . ' dla URL ' . $rssLinkArrayValue . "\n");
//
//                $reader = new Reader;
//
//                // Return a resource
//                $resource = $reader->download($rssLinkArrayValue);
//
//                // Return the right parser instance according to the feed format
//                $parser = $reader->getParser(
//                    $resource->getUrl(),
//                    $resource->getContent(),
//                    $resource->getEncoding()
//                );
//
//                // Return a Feed object
//                $feed = $parser->execute();
//
//                foreach ($feed->items as $key => $val) {
//                    $externalId = $feed->items[$key]->getId();
//                    $itemArticleFlag = $this->articleRepository->findOneBy(['externalId' => $externalId]);
//
//                    if (!$itemArticleFlag) {
//                        $article = new Article();
//
//                        //logowanie dodania pojedyńczego artykułu
//                        $this->logger->info('Dodanie atrykułu z id: ' . $feed->items[$key]->getId());
//
//                        $article->setExternalId($feed->items[$key]->getId());
//                        $article->setTitle($feed->items[$key]->getTitle());
//                        $article->setPubDate($feed->items[$key]->getPublishedDate());
//                        $article->setInsertDate($feed->items[$key]->getUpdatedDate());
//                        $article->setContent($feed->items[$key]->getContent());
//
//                        // tell Doctrine you want to (eventually) save the $article (no queries yet)
//                        $this->articleRepository->save($article);
//                    }
//                }
//
//                // actually executes the queries (i.e. the INSERT query)
//                $this->articleRepository->execQuery();
//            } catch (\Exception $e) {
//                $this->logger->info('Kod błędu odpowiedzi serwera: ' . $respCode . ' dla URL ' . $rssLinkArrayValue . "\n");
//            }
//        }
//
//        // logowanie zakończenia skryptu
//        $this->logger->info('Zakończenie wykonywania skryptu.');
//    }
}
