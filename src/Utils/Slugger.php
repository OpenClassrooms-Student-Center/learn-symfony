<?php

namespace App\Utils;

/**
 * This class transforms a Post title into a valid uri string.
 */
class Slugger
{
    public static function slugify(string $string): string
    {
        return preg_replace('/\s+/', '-', mb_strtolower(trim(strip_tags($string)), 'UTF-8'));
    }
}
