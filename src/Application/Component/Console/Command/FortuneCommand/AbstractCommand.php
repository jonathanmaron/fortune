<?php

namespace Application\Component\Console\Command\FortuneCommand;

use Application\Component\Console\Command\AbstractCommand as ParentCommand;
use Symfony\Component\Console\Terminal;

abstract class AbstractCommand extends ParentCommand
{
    protected const TERMINAL_WIDTH    = 80;

    protected const WORDWRAP_DISABLED = 0;

    protected const WORDWRAP_MIN      = 5;

    protected $wordwrap;

    protected $length;

    protected $author;

    protected $short;

    protected $long;

    protected function getWordwrapDefault()
    {
        $terminal = new Terminal();

        $width = $terminal->getWidth();

        if ($width > 0) {
            $width--;
        } else {
            $width = self::TERMINAL_WIDTH;
        }

        return $width;
    }

    protected function getWordwrap()
    {
        return $this->wordwrap;
    }

    protected function setWordwrap($wordwrap)
    {
        $this->wordwrap = (int) $wordwrap;

        return $this;
    }

    protected function getLength()
    {
        return $this->length;
    }

    protected function setLength($length)
    {
        $this->length = (int) $length;

        return $this;
    }

    protected function getAuthor()
    {
        return $this->author;
    }

    protected function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    public function getShort()
    {
        return $this->short;
    }

    public function setShort($short)
    {
        $this->short = (bool) $short;

        return $this;
    }

    public function getLong()
    {
        return $this->long;
    }

    public function setLong($long)
    {
        $this->long = (bool) $long;

        return $this;
    }
}
