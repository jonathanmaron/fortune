<?php
declare(strict_types=1);

namespace App\Fortune;

use App\Component\Finder\Finder;

class Fortune extends AbstractFortune
{
    public function getAllFortunes(): array
    {
        $ret = [];

        $finder = new Finder();
        foreach ($finder->php($this->getFortunePath()) as $fileInfo) {
            $ret += include $fileInfo->getPathname();
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
        $stack = include $this->getRandomFilename();
        $key   = array_rand($stack);

        return [
            $stack[$key][0] ?? '',
            $stack[$key][1] ?? '',
        ];
    }

    private function getRandomFilename(): string
    {
        $finder = new Finder();

        $stack = [];
        foreach ($finder->php($this->getFortunePath()) as $fileInfo) {
            $stack[] = $fileInfo->getPathname();
        }

        $key = array_rand($stack);

        return $stack[$key];
    }

    public function getRandomShortFortune(): array
    {
        $medianLength = $this->getMedianFortuneLength();

        $lengths = array_filter($this->getAllLengths(), function ($length) use ($medianLength) {
            return $medianLength > $length;
        });

        $key = array_rand($lengths);

        return $this->getRandomFortuneByLength($lengths[$key]);
    }

    public function getRandomLongFortune(): array
    {
        $medianLength = $this->getMedianFortuneLength();
        $lengths      = array_filter($this->getAllLengths(), function ($length) use ($medianLength) {
            return $medianLength < $length;
        });

        $key = array_rand($lengths);

        return $this->getRandomFortuneByLength($lengths[$key]);
    }

    private function getMedianFortuneLength(): int
    {
        $lengths = array_values($this->getAllLengths());
        sort($lengths, SORT_NUMERIC);
        $key = floor(count($lengths) / 2);

        return (int) $lengths[$key];
    }

    public function getRandomFortuneByLength(int $length): array
    {
        return $this->getRandomFortuneByKeyValue('length', $length);
    }

    public function getRandomFortuneByAuthor(string $author): array
    {
        return $this->getRandomFortuneByKeyValue('author', $author);
    }

    /**
     * @todo: Switch to union type when supporting only php 8.0 and newer
     *
     * @param string     $key
     * @param int|string $value
     *
     * @return array
     */
    private function getRandomFortuneByKeyValue(string $key, $value): array
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
        $file    = $ref[0] ?? '';
        $uuid    = $ref[1] ?? '';

        if ('' === $file || '' === $uuid) {
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
