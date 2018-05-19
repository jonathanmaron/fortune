<?php

namespace Application\Component\Finder;

use Symfony\Component\Finder\Finder as ParentFinder;

class Finder extends ParentFinder
{
    public function php($path)
    {
        $finder    = $this->files();
        $finder    = $finder->in($path);
        $fileInfos = $finder->name('*.php');

        return $fileInfos;
    }

    public function json($path)
    {
        $finder    = $this->files();
        $finder    = $finder->in($path);
        $fileInfos = $finder->name('*.json');

        return $fileInfos;
    }
}
