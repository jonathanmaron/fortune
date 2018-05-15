<?php

namespace Application\Component\Console\Command;

use Application\Component\Filesystem\Filesystem;
use Application\Component\Finder\Finder;
use Application\Exception\InvalidArgumentException;
use Application\Exception\RuntimeException;
use NumberFormatter;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FortuneImportCommand extends AbstractCommand
{
    const FORTUNES_PER_FILE = 50;

    use LockableTrait;

    protected function configure()
    {
        $this->setName('fortune-import');

        $this->setDescription('Import fortunes from JSON files');

        $name        = 'input-path';
        $shortcut    = null;
        $mode        = InputOption::VALUE_REQUIRED;
        $description = 'Path to JSON files containing fortunes';
        $default     = null;

        $this->addOption($name, $shortcut, $mode, $description, $default);

        return $this;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $inputPath = $input->getOption('input-path');

        if (!$this->lock()) {
            $message = 'The script is already running in another process.';
            throw new RuntimeException($message);
        }

        if (!is_dir($inputPath)) {
            $message = '--input-path contains an invalid path';
            throw new InvalidArgumentException($message);
        }

        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem      = new Filesystem();
        $numberFormatter = new NumberFormatter(null, NumberFormatter::DECIMAL);

        $inputPath  = $input->getOption('input-path');
        $inputPath  = realpath($inputPath);
        $outputPath = $this->getFortune()->getPath();
        $outputPath = realpath($outputPath);

        $newFortunes = $this->getNewFortunes($inputPath);
        $curFortunes = $this->getFortune()->getFortunes();

        $newFortunesCount = count($newFortunes);
        $addFortunesCount = 0;

        if (0 === $newFortunesCount) {
            throw new RuntimeException('There are no quotations to process');
        }

        $progressBar = new ProgressBar($output, $newFortunesCount);
        $progressBar->start();

        foreach ($newFortunes as $newFortune) {
            $progressBar->advance();
            $uuid = $this->uuid($newFortune[0]);
            if (array_key_exists($uuid, $curFortunes)) {
                continue;
            }
            $curFortunes[$uuid] = [
                $newFortune[0],
                $newFortune[1],
            ];
            $addFortunesCount++;
        }

        $progressBar->finish();

        if ($addFortunesCount > 0) {
            $chunks = array_chunk($curFortunes, self::FORTUNES_PER_FILE, true);
            $filesystem->dumpFiles($outputPath, "%'.05d.php", $chunks);
        }

        $curFortunesCount = count($curFortunes);

        $output->writeln('');
        $output->writeln(sprintf('Added %s %s. There are %s %s in the database.',
                                 $numberFormatter->format($addFortunesCount),
                                 (1 === $addFortunesCount) ? 'fortune' : 'fortunes',
                                 $numberFormatter->format($curFortunesCount),
                                 (1 === $curFortunesCount) ? 'fortune' : 'fortunes'));

        return $this;
    }

    private function getNewFortunes($inputPath)
    {
        $ret = [];

        $finder = new Finder();

        foreach ($finder->json($inputPath) as $fileInfo) {
            $json  = file_get_contents($fileInfo->getPathname());
            $array = json_decode($json, true);
            foreach ($array as $record) {
                $quote  = $this->filter($record['quoteText']);
                $author = $this->filter($record['quoteAuthor']);
                $uuid   = $this->uuid($quote);
                if (array_key_exists($uuid, $ret)) {
                    continue;
                }
                if (empty($quote)) {
                    continue;
                }
                if (empty($author)) {
                    $author = 'Unknown';
                }
                $ret[$uuid] = [
                    $quote,
                    $author,
                    //strlen($quote),
                ];
            }
        }

        shuffle($ret);

        return $ret;
    }

    private function filter($string)
    {
        $string = str_replace('’', "'", $string);
        $string = trim($string);

        return $string;
    }

    private function uuid($quote)
    {
        $name = strtolower($quote);
        $name = preg_replace('/[^a-z]/', null, $name);

        $uuid5 = Uuid::uuid5(Uuid::NIL, $name);

        return strtolower($uuid5->toString());
    }
}
