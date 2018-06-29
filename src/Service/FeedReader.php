<?php

namespace App\Service;

use PicoFeed\Reader\Reader;

class FeedReader
{
    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function setFeedReader(string $feedUrl): \PicoFeed\Parser\Feed
    {
        // Return a resource
        $resource = $this->reader->download($feedUrl);

        // Return the right parser instance according to the feed format
        $parser = $this->reader->getParser(
            $resource->getUrl(),
            $resource->getContent(),
            $resource->getEncoding(),
            $resource->getEnclosureUrl()
        );

        // Return a Feed object
        return $feed = $parser->execute();
    }
}
