<?php

/*
 * Wuhu fancy PCH!
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\PhpCodeHelper\Commands;

use Fluxter\PhpCodeHelper\Helper\NamespaceHelper;
use Fluxter\PhpCodeHelper\Model\PhpFileInformation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class FixUsingsCommand extends Command
{
    protected static $defaultName = 'fix-usings';

    private OutputInterface $output;

    private $filesWithClasses = [];
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
        ini_set('xdebug.max_nesting_level', 9000);
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
        $this->output->writeln('Searching files...');
        $allFiles = NamespaceHelper::getPhpFilesInPath($basePath);

        $this->output->writeln('Indexing files...');
        $progressBar = new ProgressBar($this->output);
        $progressBar->setFormat('%current%/%max% [%bar%] %percent%% %memory:6s% -- %message%');

        $progressBar->display();
        /** @var SplFileInfo $localFile */
        foreach ($progressBar->iterate($allFiles) as $localFile) {
            $progressBar->setMessage($localFile->getRealPath());
            if ('fonts' == basename($localFile->getPath())) {
                continue;
            }
            try {
                $file = new PhpFileInformation($localFile->getRealPath());
                $classes = [];
                foreach ($file->getFqdns() as $class) {
                    $classes[] = $class;
                    $this->fqdnClasses[] = $class;
                }
                $this->filesWithClasses[$localFile->getRealPath()] = $classes;
            } catch (\Exception $ex) {
                $this->output->writeln(" - File failed: {$localFile->getRealPath()} - {$ex->getMessage}");
            }
        }

        $progressBar->finish();

        $this->output->writeln('Fixing files...');

        foreach ($this->filesWithClasses as $filePath => $class) {
            if (0 === strpos($filePath, $basePath . '/vendor')) {
                // We dont want to fix the vendor dir!
                continue;
            }
            $this->fixFile($filePath);
        }
    }

    private function fixFile($filePath): void
    {
        $file = new PhpFileInformation($filePath);
        $this->output->writeln("Fixing file {$file->getFile()}");
        foreach ($file->getUsings() as $using) {
            if ($this->fqdnInStack($using)) {
                continue;
            }

            $this->output->write(" - Using not exists! $using. Searching alternative... ");
            $alternative = $this->getMostLikelyCorrectUsing($using);
            if (!$alternative) {
                $this->output->writeln('No found :(');
                continue;
            }

            $this->output->writeln("Found alternative: $alternative");
            $newContent = str_replace("use $using", "use $alternative", file_get_contents($filePath));
            file_put_contents($filePath, $newContent);
        }
    }

    private function getMostLikelyCorrectUsing($search): ?string
    {
        $searchParts = explode('\\', $search);

        $result = [];
        foreach ($this->fqdnClasses as $check) {
            $checkParts = explode('\\', $check);
            $correct = 0;
            $searchPartsIndex = count($searchParts) - 1;

            // echo "{$search} = $check";            echo "\n";
            for ($i = count($checkParts) - 1; $i > 0 && $searchPartsIndex > 0; $i--) {
                // echo "{$searchParts[$searchPartsIndex]} = {$checkParts[$i]}";
                // echo "\n";
                if ($searchParts[$searchPartsIndex] == $checkParts[$i]) {
                    $correct++;
                } else {
                    break;
                }
                $searchPartsIndex--;
            }

            if (0 != $correct) {
                $result[$check] = $correct;
            }
        }

        if (0 == count($result)) {
            return null;
        }
        arsort($result);

        return array_key_first($result);
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
