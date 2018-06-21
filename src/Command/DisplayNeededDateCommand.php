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

use App\Service\FeedService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisplayNeededDateCommand extends ContainerAwareCommand
{
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
        $output->writeln($this->feedService->setFeedToDataBase());
    }
}
