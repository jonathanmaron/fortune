<?php
declare(strict_types=1);

namespace Application\Fortune;

abstract class AbstractFortune
{
    private $fortunePath = '';

    private $indexPath   = '';

    public function getFilename($file): string
    {
        return sprintf('%s/%s', $this->getFortunePath(), $file);
    }

    public function getIndexFilename($index): string
    {
        return sprintf('%s/%s.php', $this->getIndexPath(), $index);
    }

    public function getFortunePath(): string
    {
        return $this->fortunePath;
    }

    public function setFortunePath($fortunePath): self
    {
        $this->fortunePath = $fortunePath;

        return $this;
    }

    public function getIndexPath(): string
    {
        return $this->indexPath;
    }

    public function setIndexPath($indexPath): self
    {
        $this->indexPath = $indexPath;

        return $this;
    }
}
