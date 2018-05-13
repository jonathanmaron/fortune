<?php

namespace Application\Component\Console\Command;

use Application\Component\Finder\Finder;
use Application\Exception\RuntimeException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class FortuneImportCommand extends AbstractCommand
{
    const FORTUNES_PER_FILE = 25;

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
        $inputPath = $input->getOption('input-path');
        $inputPath = realpath($inputPath);

        $outputPath = $this->getFortune()->getPath();
        $outputPath = realpath($outputPath);

        $newFortunes = $this->getNewFortunes($inputPath);
        $curFortunes = $this->getFortune()->getFortunes();

        $counter = 0;

        if (0 === count($newFortunes)) {
            throw new RuntimeException('There are no quotations to process');
        }

        foreach ($newFortunes as $newFortune) {
            $uuid = $this->uuid($newFortune[0]);
            if (array_key_exists($uuid, $curFortunes)) {
                continue;
            }
            $curFortunes[$uuid] = [
                $newFortune[0],
                $newFortune[1],
            ];
            $counter++;
        }

        $chunks = array_chunk($curFortunes, self::FORTUNES_PER_FILE, true);

        $this->write($outputPath, $chunks);

        $line = sprintf('Added %d new %s.', $counter, (1 === $counter) ? 'fortune' : 'fortunes');
        $output->writeln($line);

        return $this;
    }

    private function write($path, $chunks)
    {
        $filesystem = new Filesystem();

        foreach ($chunks as $key => $chunk) {

            $phpString = $this->getPhpEncoder()->encode($chunk);

            $filename = sprintf("%s/%'.05d.php", $path, $key + 1);
            $data     = sprintf("<?php\n\nreturn %s;\n", $phpString);

            $filesystem->dumpFile($filename, $data);
        }

        return $this;
    }

    private function normalize($string)
    {
        $string = str_replace('’', "'", $string);
        $string = trim($string);

        return $string;
    }

    private function uuid($quote)
    {
        $quote = strtolower($quote);

        $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, $quote)->toString();

        return $uuid;
    }

    private function getNewFortunes($inputPath)
    {
        $ret = [];

        $finder = new Finder();

        foreach ($finder->json($inputPath) as $fileInfo) {
            $json  = file_get_contents($fileInfo->getPathname());
            $array = json_decode($json, true);
            foreach ($array as $record) {

                $quote  = $this->normalize($record['quoteText']);
                $author = $this->normalize($record['quoteAuthor']);
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
}
