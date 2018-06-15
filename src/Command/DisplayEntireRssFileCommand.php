<?php
/**
 * Created by PhpStorm.
 * User: michalpalys
 * Date: 13.06.18
 * Time: 13:39
 */
// src/Command/DisplayEntireRssFileCommand.php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PicoFeed\Reader\Reader;
use PicoFeed\PicoFeedException;


class DisplayEntireRssFileCommand extends Command
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('rss:get-entire-rss')

            // the short description shown while running "php bin/console list"
            ->setDescription('Show title of articles.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to display all articles titles.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->printRss());
    }

    public function printRss()
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
            echo $feed;
        }
        catch (PicoFeedException $e) {
            echo "it should not happen";
        }
    }
}