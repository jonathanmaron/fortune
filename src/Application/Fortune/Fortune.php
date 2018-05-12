<?php

namespace Application\Fortune;

use Application\Component\Finder\Finder;

class Fortune
{
    private $path;

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function getRandomFortune()
    {
        $stack = include $this->getRandomFilename();
        $key   = array_rand($stack);

        return [
            'quote'  => $stack[$key][0] ?? null,
            'author' => $stack[$key][1] ?? null,
        ];
    }

    private function getRandomFilename()
    {
        $finder = new Finder();

        $stack = [];
        foreach ($finder->php($this->getPath()) as $fileInfo) {
            array_push($stack, $fileInfo->getPathname());
        }

        return $stack[array_rand($stack)];
    }
}
