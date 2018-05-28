<?php
declare(strict_types=1);

namespace Application\Component\Finder;

use Symfony\Component\Finder\Finder as ParentFinder;

class Finder extends ParentFinder
{
    public function php(string $path): self
    {
        $finder    = $this->files();
        $finder    = $finder->in($path);
        $fileInfos = $finder->name('*.php');

        return $fileInfos;
    }

    public function json(string $path): self
    {
        $finder    = $this->files();
        $finder    = $finder->in($path);
        $fileInfos = $finder->name('*.json');

        return $fileInfos;
    }
}
