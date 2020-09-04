<?php

/*
 * This file is part of the Fluxter Kundencenter package.
 * (c) Fluxter <http://fluxter.net/>
 * You are not allowed to see or use this code if you are not a part of Fluxter!
 */

namespace Fluxter\PhpCodeHelper\Helper;

use Symfony\Component\Finder\Finder;

final class NamespaceHelper
{
    public static function getClassNameFromFqdn(string $fqdn): string
    {
        $arr = explode('\\', $fqdn);
        $className = end($arr);

        return $className;
    }

    public static function getNamespaceFromFqdn(string $fqdn): string
    {
        $namespace = str_replace(self::getClassNameFromFqdn($fqdn), '', $fqdn);

        return substr($namespace, 0, strlen($namespace) - 1);
    }

    public static function getPhpFilesInPath(string $basePath)
    {
        $finder = new Finder();

        return $finder->files()->name('*.php')->in($basePath);
    }
}
