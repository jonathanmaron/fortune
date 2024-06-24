<?php
declare(strict_types=1);

namespace App\Component\Console\Command\ImportCommand;

use App\Component\Console\Command\AbstractCommand as ParentCommand;
use App\Component\Finder\Finder;
use App\Exception\InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCommand extends ParentCommand
{
    // <editor-fold desc="Class Constants">

    protected const int FORTUNES_PER_FILE = 250;

    // </editor-fold>

    // <editor-fold desc="Class Properties">

    protected string $path = '';

    // </editor-fold>

    // <editor-fold desc="Command Configuration">

    protected function configureCommand(): void
    {
        $this->setName('import');

        $this->setDescription('Import fortunes from JSON files');

        $this->setHelp('@todo: The <info>command</info> command. Example: <info>command</info>.');
    }

    protected function configurePath(): void
    {
        $name        = 'path';
        $shortcut    = null;
        $mode        = InputOption::VALUE_REQUIRED;
        $description = 'Path to JSON files containing fortunes';
        $default     = '';

        $this->addOption($name, $shortcut, $mode, $description, $default);
    }

    // </editor-fold>

    // <editor-fold desc="Option Value Validation and Setting">

    protected function initializePath(InputInterface $input): void
    {
        $path = $input->getOption('path');
        assert(is_string($path));
        $path = trim($path);

        if (!is_dir($path)) {
            $message = '--path contains an invalid path';
            throw new InvalidArgumentException($message);
        }

        $path = realpath($path);
        assert(is_string($path));

        $this->setPath($path);
    }

    // </editor-fold>

    // <editor-fold desc="Helpers">

    protected function getNewFortunes(string $inputPath): array
    {
        $ret = [];

        $finder = new Finder();

        foreach ($finder->json($inputPath) as $fileInfo) {
            $json = file_get_contents($fileInfo->getPathname());
            assert(is_string($json));
            $array = json_decode($json, true);
            assert(is_array($array));
            foreach ($array as $record) {
                assert(is_array($record));
                assert(array_key_exists('quoteText', $record));
                assert(array_key_exists('quoteAuthor', $record));
                $quote  = $this->filter($record['quoteText']);
                $author = $this->filter($record['quoteAuthor']);
                $uuid   = $this->uuid($quote);
                if (array_key_exists($uuid, $ret)) {
                    continue;
                }
                if ('' === $quote) {
                    continue;
                }
                if ('' === $author) {
                    $author = 'Unknown';
                }
                $ret[$uuid] = [$quote, $author];
            }
        }

        ksort($ret, SORT_NATURAL);

        return $ret;
    }

    protected function filter(string $string): string
    {
        $string = str_replace('’', "'", $string);

        return trim($string);
    }

    protected function uuid(string $quote): string
    {
        $name = strtolower($quote);
        $name = preg_replace('/[^a-z]/', '', $name);
        assert(is_string($name));

        $uuid5 = Uuid::uuid5(APP_UUID5_NAMESPACE, $name);

        return strtolower($uuid5->toString());
    }

    // </editor-fold>

    // <editor-fold desc="Option Getters & Setters">

    protected function getPath(): string
    {
        return $this->path;
    }

    protected function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    // </editor-fold>
}
