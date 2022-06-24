<?php
declare(strict_types=1);

namespace App\Component\Finder;

use Symfony\Component\Finder\Finder as ParentFinder;

class Finder extends ParentFinder
{
    public function php(string $path): self
    {
        $finder = $this->files();
        $finder = $finder->in($path);

        return $finder->name('*.php');
    }

    public function json(string $path): self
    {
        $finder = $this->files();
        $finder = $finder->in($path);

        return $finder->name('*.json');
    }
}
