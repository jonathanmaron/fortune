<?php
declare(strict_types=1);

namespace App\Component\Finder;

use Symfony\Component\Finder\Finder as ParentFinder;

class Finder extends ParentFinder
{
    public function php(string $path): self
    {
        return $this->files()
            ->in($path)
            ->name('*.php');
    }

    public function json(string $path): self
    {
        return $this->files()
            ->in($path)
            ->name('*.json');
    }
}
