<?php
declare(strict_types=1);

namespace App\Component\Console\Command\ImportCommand;

use App\Component\Filesystem\Filesystem;
use App\Exception\RuntimeException;
use NumberFormatter;
use Override;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends AbstractCommand
{
    #[Override]
    protected function configure(): void
    {
        $this->configureCommand();
        $this->configurePath();
    }

    #[Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->initializePath($input);
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem       = new Filesystem();
        $numberFormatter  = new NumberFormatter(locale_get_default(), NumberFormatter::DECIMAL);
        $fortune          = $this->getFortune();
        $inputPath        = $this->getPath();
        $outputPath       = $fortune->getFortunePath();
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
            $curFortunes[$uuid] = [$newFortune[0], $newFortune[1]];
            $addFortunesCount++;
        }

        $progressBar->finish();

        ksort($curFortunes, SORT_NATURAL);

        if (0 < $addFortunesCount) {
            $chunks = array_chunk($curFortunes, self::FORTUNES_PER_FILE, true);
            $filesystem->arrayExportFiles($outputPath, $chunks);
        }

        $curFortunesCount = count($curFortunes);

        $addFormatted = $numberFormatter->format($addFortunesCount);
        $curFormatted = $numberFormatter->format($curFortunesCount);
        $addNoun      = 1 === $addFortunesCount ? 'fortune' : 'fortunes';
        $curNoun      = 1 === $curFortunesCount ? 'fortune' : 'fortunes';
        $format       = 'Added %s %s. There are %s %s in the database.';
        $message      = sprintf($format, $addFormatted, $addNoun, $curFormatted, $curNoun);
        $output->writeln(['', $message]);

        return 0;
    }
}
