<?php
declare(strict_types=1);

namespace App\Fortune;

abstract class AbstractFortune
{
    private string $fortunePath = '';

    private string $indexPath   = '';

    public function getFilename(string $file): string
    {
        return sprintf('%s/%s', $this->getFortunePath(), $file);
    }

    public function getIndexFilename(string $index): string
    {
        return sprintf('%s/%s.php', $this->getIndexPath(), $index);
    }

    public function getFortunePath(): string
    {
        return $this->fortunePath;
    }

    public function setFortunePath(string $fortunePath): self
    {
        $this->fortunePath = $fortunePath;

        return $this;
    }

    public function getIndexPath(): string
    {
        return $this->indexPath;
    }

    public function setIndexPath(string $indexPath): self
    {
        $this->indexPath = $indexPath;

        return $this;
    }
}
