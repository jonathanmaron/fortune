<?php

declare(strict_types=1);

namespace Application\Fortune;

use Application\Component\Finder\Finder;

class Fortune
{
    private $fortunePath = '';

    private $indexPath   = '';

    public function getAllFortunes(): array
    {
        $ret = [];

        $finder = new Finder();
        foreach ($finder->php($this->getFortunePath()) as $fileInfo) {
            $ret = array_merge($ret, include $fileInfo->getPathname());
        }

        return $ret;
    }

    public function getAllLengths(): array
    {
        $index = include $this->getIndexFilename('length');

        return array_keys($index);
    }

    public function getAllAuthors(): array
    {
        $index = include $this->getIndexFilename('author');

        return array_keys($index);
    }

    public function getRandomFortune(): array
    {
        $stack   = include $this->getRandomFilename();
        $randKey = array_rand($stack);

        return [
            $stack[$randKey][0] ?? null,
            $stack[$randKey][1] ?? null,
        ];
    }

    public function getRandomShortFortune(): array
    {
        $medianLength = $this->getMedianFortuneLength();
        $lengths      = $this->getAllLengths();
        $lengths      = array_filter($lengths, function ($length) use ($medianLength) {
            if ($medianLength < $length) {
                return false;
            }

            return true;
        });

        $randLength = array_rand($lengths);

        return $this->getRandomFortuneByLength($randLength);
    }

    public function getRandomLongFortune(): array
    {
        $medianLength = $this->getMedianFortuneLength();
        $lengths      = $this->getAllLengths();
        $lengths      = array_filter($lengths, function ($length) use ($medianLength) {
            if ($medianLength > $length) {
                return false;
            }

            return true;
        });

        $randLength = array_rand($lengths);

        return $this->getRandomFortuneByLength($randLength);
    }

    private function getMedianFortuneLength(): int
    {
        $lengths = array_values($this->getAllLengths());
        sort($lengths, SORT_NUMERIC);
        $middleKey = floor(count($lengths) / 2);
        $ret       = (int) $lengths[$middleKey];

        return $ret;
    }

    public function getRandomFortuneByLength($length): array
    {
        return $this->getRandomFortuneByKeyValue('length', $length);
    }

    public function getRandomFortuneByAuthor($author): array
    {
        return $this->getRandomFortuneByKeyValue('author', $author);
    }

    private function getRandomFortuneByKeyValue($key, $value): ?array
    {
        $index = include $this->getIndexFilename($key);

        if (!isset($index[$value])) {
            return null;
        }

        $randKey = array_rand($index[$value]);
        $ref     = $index[$value][$randKey];
        $file    = $ref[0] ?? null;
        $uuid    = $ref[1] ?? null;

        if (null === $file || null === $uuid) {
            return null;
        }

        $filename = $this->getFilename($file);

        if (!is_readable($filename)) {
            return null;
        }

        $fortunes = include $filename;

        $fortuneArray = $fortunes[$uuid] ?? null;

        return $fortuneArray;
    }

    private function getRandomFilename(): string
    {
        $finder = new Finder();

        $stack = [];
        foreach ($finder->php($this->getFortunePath()) as $fileInfo) {
            array_push($stack, $fileInfo->getPathname());
        }

        $randKey = array_rand($stack);

        return $stack[$randKey];
    }

    public function getFilename($file): string
    {
        return sprintf('%s/%s', $this->getFortunePath(), $file);
    }

    public function getIndexFilename($index): string
    {
        return sprintf('%s/%s.php', $this->getIndexPath(), $index);
    }

    public function getFortunePath(): string
    {
        return $this->fortunePath;
    }

    public function setFortunePath($fortunePath): self
    {
        $this->fortunePath = $fortunePath;

        return $this;
    }

    public function getIndexPath(): string
    {
        return $this->indexPath;
    }

    public function setIndexPath($indexPath): self
    {
        $this->indexPath = $indexPath;

        return $this;
    }
}
