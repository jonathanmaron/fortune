<?php

declare(strict_types=1);

namespace Application\Component\Console\Command\IndexCommand;

use Application\Component\Filesystem\Filesystem;
use Application\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexCommand extends AbstractCommand
{
    protected function configure(): self
    {
        $this->setName('index');

        $this->setDescription('Build indexes');

        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output): self
    {
        $filesystem = new Filesystem();
        $finder     = new Finder();
        $fortune    = $this->getFortune();

        $outputPath = $fortune->getIndexPath();

        if ($filesystem->exists($outputPath)) {
            $filesystem->remove($outputPath);
        }

        $filesystem->mkdir($outputPath);

        $indices = [
            'length' => [],
            'author' => [],
        ];

        $fileInfos = $finder->php($fortune->getFortunePath());
        foreach ($fileInfos as $fileInfo) {
            $fortunes = include $fileInfo->getPathname();
            foreach ($fortunes as $uuid => $fortuneArray) {
                $length = strlen($fortuneArray[0]);
                $author = $fortuneArray[1];
                foreach (array_keys($indices) as $key) {
                    if (!isset($indices[$key][${$key}])) {
                        $indices[$key][${$key}] = [];
                    }
                    $indices[$key][${$key}][] = [
                        $fileInfo->getFilename(),
                        $uuid,
                    ];
                }
            }
        }

        foreach ($indices as $key => $index) {
            $outputFilename = $fortune->getIndexFilename($key);
            $filesystem->dumpFile($outputFilename, $index);
            $format  = 'Wrote "%s" index to "%s"';
            $message = sprintf($format, $key, $outputFilename);
            $output->writeln($message);
        }

        return $this;
    }
}
