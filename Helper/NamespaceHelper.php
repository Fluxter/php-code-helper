<?php

/*
 * Wuhu fancy PCH!
 * (c) Fluxter <http://fluxter.net/>
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
