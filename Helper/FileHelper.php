<?php

/*
 * This file is part of the Fluxter Kundencenter package.
 * (c) Fluxter <http://fluxter.net/>
 * You are not allowed to see or use this code if you are not a part of Fluxter!
 */

namespace Fluxter\PhpCodeHelper\Helper;

use Symfony\Component\Finder\Finder;

final class FileHelper
{
    public static function getFilesInPath(string $basePath, string $pattern)
    {
        $finder = new Finder();

        return $finder->files()->name($pattern)->in($basePath);
    }
}
