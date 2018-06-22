<?php
/**
 * Created by PhpStorm.
 * User: michalpalys
 * Date: 22.06.18
 * Time: 08:32
 */

namespace App\Service;

use PicoFeed\Reader\Reader;

class FeedReader
{
    public function setFeedReader(string $feedUrl)
    {
        $reader = new Reader;

        // Return a resource
        $resource = $reader->download($feedUrl);

        // Return the right parser instance according to the feed format
        $parser = $reader->getParser(
            $resource->getUrl(),
            $resource->getContent(),
            $resource->getEncoding()
        );

        // Return a Feed object
        return $feed = $parser->execute();
    }
}