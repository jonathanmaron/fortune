<?php

namespace Application\Component\Console\Command;

use Application\Component\Finder\Finder;
use Application\Exception\RuntimeException;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class FortuneImportCommand extends AbstractCommand
{
    const QUOTATIONS_PER_FILE = 25;

    use LockableTrait;

    protected function configure()
    {
        $this->setName('fortune-import');

        $this->setDescription('Import quotations');

        $name        = 'input-path';
        $shortcut    = null;
        $mode        = InputOption::VALUE_REQUIRED;
        $description = 'Input path.';
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
            $message = 'Invalid input path.';
            throw new RuntimeException($message);
        }

        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = new Filesystem();

        $inputPath = $input->getOption('input-path');
        $inputPath = realpath($inputPath);

        $quotations = $this->getQuotations($inputPath);

        if (0 === count($quotations)) {
            throw new RuntimeException('There are no quotations to process');
        }

        $outputPath = $this->getFortune()->getPath();

        $filesystem->remove($outputPath);
        $filesystem->mkdir($outputPath);

        shuffle($quotations);

        $chunks = array_chunk($quotations, self::QUOTATIONS_PER_FILE);

        foreach ($chunks as $key => $chunk) {

            $phpString = $this->getPhpEncoder()->encode($chunk);

            $filename = sprintf("%s/%'.05d.php", $outputPath, $key + 1);
            $data     = sprintf("<?php\n\nreturn %s;\n", $phpString);

            $filesystem->dumpFile($filename, $data);

            $line = sprintf("Written to %s.", realpath($filename));
            $output->writeln($line);
        }

        return $this;
    }

    private function normalize($string)
    {
        $string = str_replace('’', "'", $string);
        $string = trim($string);

        return $string;
    }

    private function getHash($quote)
    {
        $quote = strtolower($quote);
        $hash  = hash('sha256', $quote);

        return $hash;
    }

    private function getQuotations($inputPath)
    {
        $ret = [];

        $finder = new Finder();

        foreach ($finder->json($inputPath) as $fileInfo) {
            $json  = file_get_contents($fileInfo->getPathname());
            $array = json_decode($json, true);
            foreach ($array as $record) {
                $key    = $this->getHash($record['quoteText']);
                $quote  = $this->normalize($record['quoteText']);
                $author = $this->normalize($record['quoteAuthor']);
                if (empty($quote)) {
                    continue;
                }
                if (isset($ret[$key])) {
                    continue;
                }
                if (empty($author)) {
                    $author = 'Unknown';
                }
                $ret[$key] = [
                    $quote,
                    $author,
                    //strlen($quote),
                ];
            }
        }

        return $ret;
    }
}
