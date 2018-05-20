<?php

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
    protected function configure()
    {
        $this->setName('import');

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
        $fortune         = $this->getFortune();

        $inputPath = $input->getOption('input-path');
        $inputPath = realpath($inputPath);

        $outputPath = $fortune->getFortunePath();
        $outputPath = realpath($outputPath);

        $newFortunes = $this->getNewFortunes($inputPath);
        $curFortunes = $fortune->getAllFortunes();

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
}
