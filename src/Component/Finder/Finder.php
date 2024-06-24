<?php
declare(strict_types=1);

namespace App\Component\Finder;

use Symfony\Component\Finder\Finder as ParentFinder;

class Finder extends ParentFinder
{
    public function php(string $path): self
    {
        $files = $this->files();
        $files->in($path);
        $files->name('*.php');

        return $files;
    }

    public function json(string $path): self
    {
        $files = $this->files();
        $files->in($path);
        $files->name('*.json');

        return $files;
    }
}
