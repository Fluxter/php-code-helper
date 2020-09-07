<?php

/*
 * This file is part of the Fluxter Kundencenter package.
 * (c) Fluxter <http://fluxter.net/>
 * You are not allowed to see or use this code if you are not a part of Fluxter!
 */

namespace Fluxter\PhpCodeHelper\Commands;

use Fluxter\PhpCodeHelper\Helper\FileHelper;
use Fluxter\PhpCodeHelper\Helper\LikenessHelper;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class FixSymfonyRoutesCommand extends Command
{
    protected static $defaultName = 'fix-sf-routes';

    private OutputInterface $output;

    private $routes = [];
    private $failed = [];

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Fixes automatic generated symfony routes')
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
        $this->output->write('Loading SF Routes... ');
        $this->routes = $this->getRoutes($basePath);
        $this->output->writeln('Found ' . count($this->routes) . ' routes in your application!');

        $this->fixFileByExtension($basePath, 'php');
        $this->fixFileByExtension($basePath, 'html.twig');

        if (count($this->failed)) {
            $this->output->writeln('Some routes still couldnt be found.');
            foreach ($this->failed as $file => $routes) {
                $this->output->writeln("Datei: $file");
                foreach ($routes as $route) {
                    $this->output->writeln("  - $route");
                }
            }
        }
    }

    private function fixFileByExtension(string $basePath, string $extension)
    {
        $this->output->writeln('Fixing .' . $extension . ' files...');
        $progressBar = new ProgressBar($this->output);
        $progressBar->setFormat('%current%/%max% [%bar%] %percent%% %memory:6s% -- %message%');

        $progressBar->display();
        foreach ($progressBar->iterate(FileHelper::getFilesInPath($basePath, '*.' . $extension)) as $localFile) {
            if (0 === strpos($localFile, $basePath . '/vendor')) {
                // We dont want to fix the vendor dir!
                continue;
            }
            if (0 === strpos($localFile, $basePath . '/node_modules')) {
                // We dont want to fix the vendor dir!
                continue;
            }

            $progressBar->setMessage($localFile);
            $this->fixFile($localFile);
        }
        $progressBar->finish();
    }

    private function fixFile(SplFileInfo $file): void
    {
        $content = file_get_contents($file->getRealPath());
        $routes = $this->getRoutesInFile($file->getRealPath(), $content);
        foreach ($routes as $route) {
            if (array_key_exists($route, $this->routes)) {
                continue;
            }

            // Not found, lets search it
            $alternative = LikenessHelper::getAlike($route, $this->routes, '_');
            if ($alternative) {
                $content = str_replace($route, $alternative, $content);
                file_put_contents($file->getRealPath(), $content);
            } else {
                if (!array_key_exists($file->getRealPath(), $this->failed)) {
                    $this->failed[$file->getRealPath()] = [];
                }
                $this->failed[$file->getRealPath()][] = $route;
            }
        }
    }

    private function getRoutesInFile(string $filepath, string $fileContent): array
    {
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);
        $routes = [];

        if ('php' == $extension) {
            preg_match_all("/->redirectToRoute\([\"\'](.*?)[\"\']/", $fileContent, $matches);
            if (0 != count($matches[1])) {
                foreach ($matches[1] as $route) {
                    $routes[] = $route;
                }
            }
        }
        if ('twig' == $extension) {
            preg_match_all("/path\([\"\'](.*?)[\"\']/", $fileContent, $matches);
            if (0 != count($matches[1])) {
                foreach ($matches[1] as $route) {
                    $routes[] = $route;
                }
            }

            preg_match_all("/url\([\"\'](.*)[\"\']/", $fileContent, $matches);
            if (0 != count($matches[1])) {
                foreach ($matches[1] as $route) {
                    $routes[] = $route;
                }
            }
        }

        return $routes;
    }

    private function getRoutes(string $basePath)
    {
        $p = new Process([$basePath . '/bin/console', 'debug:router', '--format=json']);
        $p->start();
        foreach ($p as $type => $data) {
            $routes = [];
            $allRoutes = json_decode($data, true);
            foreach (json_decode($data, true) as $route => $data) {
                $routes[] = $route;
            }

            return $routes;
        }
    }
}
