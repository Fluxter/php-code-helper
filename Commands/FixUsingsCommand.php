<?php

/*
 * This file is part of the Fluxter Kundencenter package.
 * (c) Fluxter <http://fluxter.net/>
 * You are not allowed to see or use this code if you are not a part of Fluxter!
 */

namespace Fluxter\PhpCodeHelper\Commands;

use Fluxter\PhpCodeHelper\Helper\NamespaceHelper;
use Fluxter\PhpCodeHelper\Model\PhpFileInformation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class FixUsingsCommand extends Command
{
    protected static $defaultName = 'fix-usings';

    private OutputInterface $output;

    private $loadedFiles = [];
    private $fqdnClasses = [];

    public function __construct()
    {
        $this->fs = new FileSystem();
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Fixed the namespace given by the composer.json file')
            ->addArgument('path', InputArgument::REQUIRED, 'Basepath of all files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $basePath = $input->getArgument('path');
        if (!file_exists($basePath)) {
            throw new \Exception('Invalid path!');
        }

        $this->fixPath(realpath($basePath));

        return 0;
    }

    private function fixPath(string $basePath)
    {
        $allFiles = NamespaceHelper::getPhpFilesInPath($basePath);

        foreach ($allFiles as $filePath) {
            $file = new PhpFileInformation($filePath);
            if (null == $file->getFqdn()) {
                continue;
            }

            $this->fqdnClasses[] = $file->getFqdn();
            $this->loadedFiles[] = $file;
        }

        /** @var PhpFileInformation $file */
        foreach ($this->loadedFiles as $file) {
            if (strpos($file->getFile(), $basePath . "/vendor") === 0) {
                // We dont want to fix the vendor dir!
                continue;
            }
            $this->fixFile($file);
        }
    }

    private function fixFile(PhpFileInformation $file): void
    {
        $this->output->writeln("Fixing file {$file->getFile()}");
        foreach ($file->getUsings() as $using) {
            if ($this->fqdnInStack($using)) {
                continue;
            }

            $this->output->writeln(" - Using not exists! $using");
        }
    }

    private function fqdnInStack($search)
    {
        foreach ($this->fqdnClasses as $s) {
            if ($search == $s) {
                return true;
            }
        }

        return false;
    }
}
