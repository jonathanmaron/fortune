<?php
declare(strict_types=1);

namespace App\Fortune;

use App\Component\Finder\Finder;

class Fortune extends AbstractFortune
{
    /**
     * @return array<string, array{string, string}>
     */
    public function getAllFortunes(): array
    {
        $ret = [];

        $finder = new Finder();
        foreach ($finder->php($this->getFortunePath()) as $fileInfo) {
            $ret += $this->loadFortunes($fileInfo->getPathname());
        }

        return $ret;
    }

    /**
     * @return list<int>
     */
    public function getAllLengths(): array
    {
        $filename = $this->getIndexFilename('length');

        if (!is_readable($filename)) {
            return [];
        }

        return array_map(intval(...), array_keys($this->loadIndex($filename)));
    }

    /**
     * @return list<string>
     */
    public function getAllAuthors(): array
    {
        $filename = $this->getIndexFilename('author');

        if (!is_readable($filename)) {
            return [];
        }

        return array_map(strval(...), array_keys($this->loadIndex($filename)));
    }

    /**
     * @return array{string, string}
     */
    public function getRandomFortune(): array
    {
        $stack = $this->loadFortunes($this->getRandomFilename());
        $key   = array_rand($stack);

        return [$stack[$key][0], $stack[$key][1]];
    }

    /**
     * @return array{string, string}
     */
    public function getRandomShortFortune(): array
    {
        $medianLength = $this->getMedianFortuneLength();

        $lengths = array_filter($this->getAllLengths(), fn(int $length): bool => $medianLength > $length);

        $key = array_rand($lengths);

        return $this->getRandomFortuneByLength($lengths[$key]);
    }

    /**
     * @return array{string, string}
     */
    public function getRandomLongFortune(): array
    {
        $medianLength = $this->getMedianFortuneLength();

        $lengths = array_filter($this->getAllLengths(), fn(int $length): bool => $medianLength < $length);

        $key = array_rand($lengths);

        return $this->getRandomFortuneByLength($lengths[$key]);
    }

    /**
     * @return array{string, string}
     */
    public function getRandomFortuneByLength(int $length): array
    {
        return $this->getRandomFortuneByKeyValue('length', $length);
    }

    /**
     * @return array{string, string}
     */
    public function getRandomFortuneByAuthor(string $author): array
    {
        return $this->getRandomFortuneByKeyValue('author', $author);
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

    private function getMedianFortuneLength(): int
    {
        $lengths = $this->getAllLengths();
        sort($lengths, SORT_NUMERIC);
        $key = intdiv(count($lengths), 2);

        return $lengths[$key];
    }

    /**
     * @return array{string, string}
     */
    private function getRandomFortuneByKeyValue(string $key, int|string $value): array
    {
        $filename = $this->getIndexFilename($key);

        if (!is_readable($filename)) {
            return ['', ''];
        }

        $index = $this->loadIndex($filename);

        if (!isset($index[$value])) {
            return ['', ''];
        }

        $references = $index[$value];
        $randKey    = array_rand($references);
        $reference  = $references[$randKey];
        $file       = $reference[0];
        $uuid       = $reference[1];

        if ('' === $file || '' === $uuid) {
            return ['', ''];
        }

        $filename = $this->getFilename($file);

        if (!is_readable($filename)) {
            return ['', ''];
        }

        $fortunes = $this->loadFortunes($filename);

        return $fortunes[$uuid] ?? ['', ''];
    }

    /**
     * Loads a fortune data file as a map of UUID to quote/author pairs.
     *
     * @return array<string, array{string, string}>
     */
    private function loadFortunes(string $filename): array
    {
        $data = include $filename;
        assert(is_array($data));

        $fortunes = [];
        foreach ($data as $uuid => $pair) {
            assert(is_string($uuid));
            assert(is_array($pair));
            $quote  = $pair[0];
            $author = $pair[1];
            assert(is_string($quote));
            assert(is_string($author));
            $fortunes[$uuid] = [$quote, $author];
        }

        return $fortunes;
    }

    /**
     * Loads an index data file as a map of index value to file/UUID references.
     *
     * @return array<int|string, list<array{string, string}>>
     */
    private function loadIndex(string $filename): array
    {
        $data = include $filename;
        assert(is_array($data));

        $index = [];
        foreach ($data as $value => $references) {
            assert(is_array($references));
            $list = [];
            foreach ($references as $reference) {
                assert(is_array($reference));
                $file = $reference[0];
                $uuid = $reference[1];
                assert(is_string($file));
                assert(is_string($uuid));
                $list[] = [$file, $uuid];
            }

            $index[$value] = $list;
        }

        return $index;
    }
}
