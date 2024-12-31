<?php

namespace App;

class CrawlerUtil
{
    public static function getPageContent($url)
    {
        $options = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: Mozilla/5.0"
            ]
        ];
        $context = stream_context_create($options);
        $content = file_get_contents($url, false, $context);
        if (!$content) {
            return null;
        }
        return $content;
    }
}
