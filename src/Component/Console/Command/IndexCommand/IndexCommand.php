<?php
declare(strict_types=1);

namespace App\Component\Console\Command\IndexCommand;

use App\Component\Filesystem\Filesystem;
use App\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexCommand extends AbstractCommand
{
    #[\Override]
    protected function configure(): void
    {
        $this->configureCommand();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
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
            assert(is_array($fortunes));
            foreach ($fortunes as $uuid => $fortuneArray) {
                assert(is_array($fortuneArray));
                $quote  = $fortuneArray[0];
                $author = $fortuneArray[1];
                assert(is_string($quote));
                assert(is_string($author));
                $reference                           = [$fileInfo->getFilename(), $uuid];
                $indices['length'][strlen($quote)][] = $reference;
                $indices['author'][$author][]        = $reference;
            }
        }

        foreach ($indices as $key => $index) {
            ksort($index, SORT_NATURAL);
            $outputFilename = $fortune->getIndexFilename($key);
            $filesystem->arrayExportFile($outputFilename, $index);
            $format  = 'Wrote "%s" index to "%s"';
            $message = sprintf($format, $key, $outputFilename);
            $output->writeln($message);
        }

        return 0;
    }
}
