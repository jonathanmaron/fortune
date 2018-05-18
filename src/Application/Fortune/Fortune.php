<?php

namespace Application\Fortune;

use Application\Component\Finder\Finder;

class Fortune
{
    private $fortunePath;

    private $indexPath;

    public function getAllFortunes()
    {
        $ret = [];

        $finder = new Finder();
        foreach ($finder->php($this->getFortunePath()) as $fileInfo) {
            $ret = array_merge($ret, include $fileInfo->getPathname());
        }

        return $ret;
    }

    public function getAllLengths()
    {
        $index = include $this->getIndexFilename('length');

        return array_keys($index);
    }

    public function getAllAuthors()
    {
        $index = include $this->getIndexFilename('author');

        return array_keys($index);
    }

    public function getRandomFortune()
    {
        $stack   = include $this->getRandomFilename();
        $randKey = array_rand($stack);

        return [
            $stack[$randKey][0] ?? null,
            $stack[$randKey][1] ?? null,
        ];
    }

    public function getRandomFortuneByLength($length)
    {
        return $this->getRandomFortuneByKeyValue('length', $length);
    }

    public function getRandomFortuneByAuthor($author)
    {
        return $this->getRandomFortuneByKeyValue('author', $author);
    }

    private function getRandomFortuneByKeyValue($key, $value)
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

    private function getRandomFilename()
    {
        $finder = new Finder();

        $stack = [];
        foreach ($finder->php($this->getFortunePath()) as $fileInfo) {
            array_push($stack, $fileInfo->getPathname());
        }

        $randKey = array_rand($stack);

        return $stack[$randKey];
    }

    public function getFilename($file)
    {
        return sprintf('%s/%s', $this->getFortunePath(), $file);
    }

    public function getIndexFilename($index)
    {
        return sprintf('%s/%s.php', $this->getIndexPath(), $index);
    }

    public function getFortunePath()
    {
        return $this->fortunePath;
    }

    public function setFortunePath($fortunePath)
    {
        $this->fortunePath = $fortunePath;

        return $this;
    }

    public function getIndexPath()
    {
        return $this->indexPath;
    }

    public function setIndexPath($indexPath)
    {
        $this->indexPath = $indexPath;

        return $this;
    }
}
