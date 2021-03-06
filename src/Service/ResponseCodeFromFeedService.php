<?php

namespace App\Service;

class ResponseCodeFromFeedService
{
    public function getResponseCodeFromFeed(string $feedLink): int
    {
        //checking answer from server
        $ch = curl_init($feedLink);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $execCurl = curl_exec($ch);

        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $info;
    }
}
