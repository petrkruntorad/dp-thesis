<?php

namespace App\Services;

class MediaService
{

    public function getYoutubeVideoIdFromUrl(string $url): string
    {
        $videoId = null;

        //splits url by /
        $explodedUrl = explode("/", $url);

        //gets last element of array
        $valueArray = end($explodedUrl);

        //last value in url array by ?
        $splittedValuesArray = explode('?', $valueArray);

        if(array_key_exists(0, $splittedValuesArray))
        {
            $videoId = $splittedValuesArray[0];
        }

        return $videoId;
    }
}
