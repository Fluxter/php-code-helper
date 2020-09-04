<?php

/*
 * This file is part of the Fluxter Kundencenter package.
 * (c) Fluxter <http://fluxter.net/>
 * You are not allowed to see or use this code if you are not a part of Fluxter!
 */

namespace Fluxter\PhpCodeHelper\Model;

class PhpFileInformation
{
    private string $file;
    private ?string $content = null;

    private const REGEX_NAMESPACE = '/\nnamespace (.*);/';
    private const REGEX_CLASS = '/\n(?:(abstract |final |))(?:class|interface) ([a-zA-Z0-9\\\\]+)/';
    private const REGEX_USING = '/\nuse ([a-zA-Z0-9\\\\]+)|\nuse ([a-zA-Z0-9\\\\]+) as .*;$/s';

    public function __construct(string $file)
    {
        $this->file = $file;
        $this->getUsings();
    }

    public function getNamespace()
    {
        $content = $this->getContent();
        preg_match(self::REGEX_NAMESPACE, $content, $matches);

        return array_key_exists(1, $matches) ? $matches[1] : null;
    }

    public function getClass(): ?string
    {
        $content = $this->getContent();
        preg_match(self::REGEX_CLASS, $content, $matches);

        return array_key_exists(1, $matches) ? $matches[1] : null;
    }

    public function getFqdn(): ?string
    {
        try {
            $class = $this->getClass();
            $namespace = $this->getNamespace();

            return $namespace && $class ? "$namespace\\$class" : null;
        } catch (\Throwable $ex) {
            return null;
        }
    }

    public function getUsings()
    {
        $content = $this->getContent();
        preg_match_all(self::REGEX_USING, $content, $matches);

        return $matches[1];
    }

    public function getContent()
    {
        if (null == $this->content) {
            $this->content = file_get_contents($this->file);
        }

        return $this->content;
    }

    /**
     * Get the value of file.
     */
    public function getFile()
    {
        return $this->file;
    }
}
