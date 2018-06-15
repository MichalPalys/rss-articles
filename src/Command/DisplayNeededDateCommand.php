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

//use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PicoFeed\Reader\Reader;
use PicoFeed\PicoFeedException;
use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class DisplayNeededDateCommand extends ContainerAwareCommand
{
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
        //$rssmodel = new RssModel();
        $output->writeln($this->printRss());
    }

    public function outOfDateArticle(): void
    {

    }

    public function printRss()
    {
        $rssLinkArray = [
            'http://www.rmf24.pl/sport/feed',
            'http://www.komputerswiat.pl/rss-feeds/komputer-swiat-feed.aspx',
            'http://xmoon.pl/rss/rss.xml',
        ];

        foreach ($rssLinkArray as $rssLinkArrayValue) {

            try {

                $reader = new Reader;

                // Return a resource
                $resource = $reader->download($rssLinkArrayValue);

                // Return the right parser instance according to the feed format
                $parser = $reader->getParser(
                    $resource->getUrl(),
                    $resource->getContent(),
                    $resource->getEncoding()
                );

                // Return a Feed object
                $feed = $parser->execute();

                // Print the feed properties with the magic method __toString()
                //$numberOfItems = count($feed->items);

                $entityManager = $this->getContainer()->get('doctrine')->getEntityManager();

//            foreach ($feed->items as $key=>$val) {
//
//                $externalId = $feed->items[$key]->getId();
////                $title = $feed->items[$key]->getTitle();
////                echo $externalId . "\n";
////                echo $title . "\n";
//                $itemArticle = $this->getContainer()->get('doctrine')->getRepository(Article::class)->findOneBy(['externalId' => $externalId]);
//
//                echo var_dump($itemArticle) . "\n";
//            }


//            $etag = $resource->getEtag();
//            $last_modified = $resource->getLastModified();
//            echo var_dump($etag) . "\n";
//            echo var_dump($last_modified) . "\n";

                foreach ($feed->items as $key => $val) {

                    $externalId = $feed->items[$key]->getId();
                    $itemArticleFlag = $this->getContainer()->get('doctrine')->getRepository(Article::class)->findOneBy(['externalId' => $externalId]);

                    if (!$itemArticleFlag) {

                        $article = new Article();

                        $article->setExternalId($feed->items[$key]->getId());
                        $article->setTitle($feed->items[$key]->getTitle());
                        $article->setPubDate($feed->items[$key]->getPublishedDate());
                        $article->setInsertDate($feed->items[$key]->getUpdatedDate());
                        $article->setContent($feed->items[$key]->getContent());

                        // tell Doctrine you want to (eventually) save the $article (no queries yet)
                        $entityManager->persist($article);
                    }
                }

                // actually executes the queries (i.e. the INSERT query)
                $entityManager->flush();

            } catch (PicoFeedException $e) {
                echo "it should not happen";
            }
        }
    }
}