<?php

declare(strict_types=1);

namespace Application\Component\Console\Command\FortuneCommand;

use Application\Component\Console\Command\AbstractCommand as ParentCommand;
use Symfony\Component\Console\Terminal;

abstract class AbstractCommand extends ParentCommand
{
    protected const TERMINAL_WIDTH    = 80;

    protected const WORDWRAP_DISABLED = 0;

    protected const WORDWRAP_MIN      = 5;

    protected const WAIT_MIN          = 0;

    protected const WAIT_MAX          = 60;

    protected $wordwrap = 0;

    protected $length   = 0;

    protected $wait     = 0;

    protected $author   = '';

    protected $short    = false;

    protected $long     = false;

    protected function getWordwrapDefault(): int
    {
        $terminal = new Terminal();

        $width = $terminal->getWidth();

        if ($width > 0) {
            $width--;
        } else {
            $width = self::TERMINAL_WIDTH;
        }

        return (int) $width;
    }

    protected function getWordwrap(): int
    {
        return $this->wordwrap;
    }

    protected function setWordwrap(int $wordwrap): self
    {
        $this->wordwrap = (int) $wordwrap;

        return $this;
    }

    protected function getLength(): int
    {
        return $this->length;
    }

    protected function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    protected function getWait(): int
    {
        return $this->wait;
    }

    protected function setWait(int $wait): self
    {
        $this->wait = $wait;

        return $this;
    }

    protected function getAuthor(): string
    {
        return $this->author;
    }

    protected function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    protected function getShort(): bool
    {
        return $this->short;
    }

    protected function setShort(bool $short): self
    {
        $this->short = $short;

        return $this;
    }

    protected function getLong(): bool
    {
        return $this->long;
    }

    protected function setLong(bool $long): self
    {
        $this->long = (bool) $long;

        return $this;
    }
}
