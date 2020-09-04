<?php

/*
 * Wuhu fancy PCH!
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\PhpCodeHelper\Commands;

use Fluxter\PhpCodeHelper\Helper\NamespaceHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class FixNamespaceCommand extends Command
{
    protected static $defaultName = 'fix-namespaces';

    private OutputInterface $output;

    public function __construct()
    {
        $this->fs = new FileSystem();
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Fixed the namespace given by the composer.json file')
            ->addArgument('path', InputArgument::REQUIRED, 'Basepath to the composer file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $basePath = $input->getArgument('path');
        if (!$basePath || !file_exists($basePath . '/composer.json')) {
            throw new \Exception('Invalid path!');
        }

        $this->fixPath($basePath);

        return 0;
    }

    private function fixPath(string $path)
    {
        $this->output->writeln("Running in path: ${path}");
        $composer = json_decode(file_get_contents($path . '/composer.json'), true);

        foreach ($composer['autoload']['psr-4'] as $psr4Namespace => $psr4Path) {
            $phpFilePath = realpath($path . '/' . $psr4Path);

            /** @var SplFileInfo $file */
            foreach (NamespaceHelper::getPhpFilesInPath($phpFilePath) as $file) {
                $this->fixFile($phpFilePath, $psr4Namespace, $file->getRealPath());
            }
        }
    }

    private function fixFile(string $basePath, string $baseNamespace, string $absoluteFilePath)
    {
        $this->output->write('- Processing file ' . $absoluteFilePath . '... ');
        $fqdn = $baseNamespace . str_replace('/', '\\', str_replace($basePath, '', str_replace('.php', '', $absoluteFilePath)));
        $fqdn = str_replace('\\\\', '\\', $fqdn);
        $namespace = NamespaceHelper::getNamespaceFromFqdn($fqdn);
        $className = NamespaceHelper::getClassNameFromFqdn($fqdn);

        $fileContent = file_get_contents($absoluteFilePath);
        if (strpos($fileContent, "namespace $namespace;")) {
            $this->output->writeln('Ok!');

            return;
        }
        $this->output->write('Fixing... ');
        $newFileContent = preg_replace('/namespace (.*);/', "namespace $namespace;", $fileContent);
        file_put_contents($absoluteFilePath, $newFileContent);
        $this->output->writeln('Done!');
    }
}
