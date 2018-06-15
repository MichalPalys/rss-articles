<?php

namespace App\Model;

//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use PicoFeed\Reader\Reader;
use PicoFeed\PicoFeedException;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;



class RssModel
{
    public function printAllRss(EntityManagerInterface $entityManager)
    {
        try {

            $reader = new Reader;

            // Return a resource
            $resource = $reader->download('http://www.rmf24.pl/sport/feed');

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

            //$entityManager = $this->getContainer()->get('doctrine');
            //$entityManager = $this->getDoctrine()->getManager();

            $article = new Article();

            foreach ($feed->items as $key=>$val) {
                $article->setExternalId( $feed->items[$key]->getId() );
                $article->setTitle( $feed->items[$key]->getTitle() );
                $article->setPubDate( $feed->items[$key]->getPublishedDate() );
                $article->setInsertDate( $feed->items[$key]->getUpdatedDate() );
                $article->setContent( $feed->items[$key]->getContent() );

                $entityManager = $managerRegistry->getManagerForClass(get_class($article));

                // tell Doctrine you want to (eventually) save the $article (no queries yet)
                $entityManager->persist($article);

                // actually executes the queries (i.e. the INSERT query)
                $entityManager->flush();
            }

        }
        catch (PicoFeedException $e) {
            echo "it should not happen";
        }
    }
}