<?php

namespace Application\Component\Finder;

use Symfony\Component\Finder\Finder as ParentFinder;

class Finder extends ParentFinder
{
    public function php($path)
    {
        return $this->files()->in($path)->name('*.php');
    }

    public function json($path)
    {
        return $this->files()->in($path)->name('*.json');
    }
}
