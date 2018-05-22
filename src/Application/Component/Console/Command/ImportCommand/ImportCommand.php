<?php

declare(strict_types=1);

namespace Application\Component\Console\Command\ImportCommand;

use Application\Component\Filesystem\Filesystem;
use Application\Exception\InvalidArgumentException;
use Application\Exception\RuntimeException;
use NumberFormatter;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends AbstractCommand
{
    protected function configure(): self
    {
        $this->setName('import');

        $this->setDescription('Import fortunes from JSON files');

        $this->setHelp('@todo: The <info>command</info> command. Example: <info>command</info>.');

        // <editor-fold desc="Option: (string) path">

        $name        = 'path';
        $shortcut    = null;
        $mode        = InputOption::VALUE_REQUIRED;
        $description = 'Path to JSON files containing fortunes';
        $default     = '';

        $this->addOption($name, $shortcut, $mode, $description, $default);

        //</editor-fold>

        return $this;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): self
    {
        // <editor-fold desc="Option: (string) path">

        $path = (string) $input->getOption('path');
        $path = trim($path);

        if (!is_dir($path)) {
            $message = '--path contains an invalid path';
            throw new InvalidArgumentException($message);
        }

        $path = (string) $path;

        $this->setPath(realpath($path));

        //</editor-fold>

        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output): self
    {
        $filesystem      = new Filesystem();
        $numberFormatter = new NumberFormatter(locale_get_default(), NumberFormatter::DECIMAL);
        $fortune         = $this->getFortune();

        $inputPath  = $this->getPath();
        $outputPath = $fortune->getFortunePath();

        $newFortunes      = $this->getNewFortunes($inputPath);
        $newFortunesCount = count($newFortunes);
        $curFortunes      = $fortune->getAllFortunes();
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

        $addFormatted = $numberFormatter->format($addFortunesCount);
        $curFormatted = $numberFormatter->format($curFortunesCount);
        $addNoun      = (1 === $addFortunesCount) ? 'fortune' : 'fortunes';
        $curNoun      = (1 === $curFortunesCount) ? 'fortune' : 'fortunes';
        $format       = 'Added %s %s. There are %s %s in the database.';
        $message      = sprintf($format, $addFormatted, $addNoun, $curFormatted, $curNoun);
        $output->writeln(['', $message]);

        return $this;
    }
}
