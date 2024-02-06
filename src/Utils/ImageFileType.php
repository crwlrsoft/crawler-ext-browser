<?php

namespace Crwlr\CrawlerExtBrowser\Utils;

enum ImageFileType: string
{
    case Png = 'png';

    case Jpeg = 'jpeg';

    case Gif = 'gif';

    case Webp = 'webp';

    public static function fromFilePath(string $filePath): ?self
    {
        $lowerCase = strtolower($filePath);

        $fourLastChars = substr($lowerCase, -4, 4);

        $fiveLastChars = substr($lowerCase, -5, 5);

        if ($fourLastChars === '.png') {
            return self::Png;
        } elseif ($fiveLastChars === '.jpeg' || $fourLastChars === 'jpg') {
            return self::Jpeg;
        } elseif ($fourLastChars === '.gif') {
            return self::Gif;
        } elseif ($fiveLastChars === '.webp') {
            return self::Webp;
        }

        return null;
    }
}
