<?php
declare(strict_types=1);

namespace Application\Fortune;

use Application\Component\Finder\Finder;

class Fortune extends AbstractFortune
{
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
        $filename = $this->getIndexFilename('length');

        if (!is_readable($filename)) {
            return [];
        }

        $index = include $filename;

        return array_keys($index);
    }

    public function getAllAuthors(): array
    {
        $filename = $this->getIndexFilename('author');

        if (!is_readable($filename)) {
            return [];
        }

        $index = include $filename;

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

    public function getRandomShortFortune(): array
    {
        $medianLength = $this->getMedianFortuneLength();
        $lengths      = $this->getAllLengths();

        $lengths = array_filter($lengths, function ($length) use ($medianLength) {
            if ($medianLength < $length) {
                return false;
            }

            return true;
        });

        $randomKey = array_rand($lengths);

        return $this->getRandomFortuneByLength($lengths[$randomKey]);
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

        $randomKey = array_rand($lengths);

        return $this->getRandomFortuneByLength($lengths[$randomKey]);
    }

    private function getMedianFortuneLength(): int
    {
        $lengths = array_values($this->getAllLengths());
        sort($lengths, SORT_NUMERIC);
        $middleKey = floor(count($lengths) / 2);

        return (int) $lengths[$middleKey];
    }

    public function getRandomFortuneByLength($length): array
    {
        return $this->getRandomFortuneByKeyValue('length', $length);
    }

    public function getRandomFortuneByAuthor($author): array
    {
        return $this->getRandomFortuneByKeyValue('author', $author);
    }

    private function getRandomFortuneByKeyValue($key, $value): array
    {
        $filename = $this->getIndexFilename($key);

        if (!is_readable($filename)) {
            return [];
        }

        $index = include $filename;

        if (!isset($index[$value])) {
            return [];
        }

        $randKey = array_rand($index[$value]);
        $ref     = $index[$value][$randKey];
        $file    = $ref[0] ?? null;
        $uuid    = $ref[1] ?? null;

        if (null === $file || null === $uuid) {
            return [];
        }

        $filename = $this->getFilename($file);

        if (!is_readable($filename)) {
            return [];
        }

        $fortunes = include $filename;

        return $fortunes[$uuid] ?? [];
    }
}
