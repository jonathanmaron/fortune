<?php

declare(strict_types=1);

namespace Application\Component\Console\Command\ImportCommand;

use Application\Component\Console\Command\AbstractCommand as ParentCommand;
use Application\Component\Finder\Finder;
use Ramsey\Uuid\Uuid;

abstract class AbstractCommand extends ParentCommand
{
    protected const FORTUNES_PER_FILE = 50;

    protected $path = '';

    protected function getNewFortunes($inputPath): array
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
                ];
            }
        }

        shuffle($ret);

        return $ret;
    }

    protected function filter(string $string): string
    {
        $string = str_replace('’', "'", $string);
        $string = trim($string);

        return $string;
    }

    protected function uuid(string $quote): string
    {
        $name = strtolower($quote);
        $name = preg_replace('/[^a-z]/', null, $name);

        $uuid5 = Uuid::uuid5(Uuid::NIL, $name);

        return strtolower($uuid5->toString());
    }

    protected function getPath(): string
    {
        return $this->path;
    }

    protected function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }
}
