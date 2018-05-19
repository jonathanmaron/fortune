<?php

namespace Application\Component\Console\Command;

use Application\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class FortuneCommand extends AbstractCommand
{
    private $wordwrap;

    private $length;

    private $author;

    private const TERMINAL_WIDTH    = 80;

    private const WORDWRAP_DISABLED = 0;

    private const WORDWRAP_MIN      = 5;

    protected function configure()
    {
        $this->setName('fortune');

        $this->setDescription('Unix-style fortune program that displays a random quotation.');

        $name        = 'wordwrap';
        $shortcut    = 'w';
        $mode        = InputOption::VALUE_REQUIRED;
        $description = 'Wordwrap at n th character. Disable with 0.';
        $default     = $this->getWordwrapDefault();

        $this->addOption($name, $shortcut, $mode, $description, $default);

        $name        = 'length';
        $shortcut    = 'l';
        $mode        = InputOption::VALUE_OPTIONAL;
        $description = 'Length of quotation';
        $default     = '';

        $this->addOption($name, $shortcut, $mode, $description, $default);

        $name        = 'author';
        $shortcut    = 'a';
        $mode        = InputOption::VALUE_OPTIONAL;
        $description = 'Author of quotation';
        $default     = '';

        $this->addOption($name, $shortcut, $mode, $description, $default);

        return $this;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $wordwrap = $input->getOption('wordwrap');
        $fortune  = $this->getFortune();

        if (!is_numeric($wordwrap)) {
            $format  = '--wordwrap must be a digit between %s and %s';
            $message = sprintf($format, self::WORDWRAP_MIN, $this->getWordwrapDefault());
            throw new InvalidArgumentException($message);
        }

        if ($wordwrap > self::WORDWRAP_DISABLED) {

            $wordwrapDefault = $this->getWordwrapDefault();

            if ($wordwrap > $wordwrapDefault) {
                $wordwrap = $wordwrapDefault;
            }

            if ($wordwrap < self::WORDWRAP_MIN) {
                $format  = '--wordwrap must be greater than or equal to %s.';
                $message = sprintf($format, self::WORDWRAP_MIN);
                throw new InvalidArgumentException($message);
            }

            if ($wordwrap > $this->getWordwrapDefault()) {
                $format  = '--wordwrap must be less than or equal to %s.';
                $message = sprintf($format, $this->getWordwrapDefault());
                throw new InvalidArgumentException($message);
            }
        }

        $this->setWordwrap($wordwrap);

        $length = $input->getOption('length');
        $length = trim($length);

        if (!empty($length)) {

            if (!is_numeric($length)) {
                $message = '--length must be a digit';
                throw new InvalidArgumentException($message);
            }

            if (!in_array($length, $fortune->getAllLengths())) {
                $message = '--length contains an invalid length';
                throw new InvalidArgumentException($message);
            }
        }

        $this->setLength($length);

        $author = $input->getOption('author');
        $author = trim($author);

        if (!empty($author)) {
            if (!in_array($author, $fortune->getAllAuthors())) {
                $message = '--author contains an invalid author';
                throw new InvalidArgumentException($message);
            }
        }

        $this->setAuthor($author);

        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fortune = $this->getFortune();
        $length  = $this->getLength();
        $author  = $this->getAuthor();

        if (!empty($length)) {
            $fortuneArray = $fortune->getRandomFortuneByLength($length);
        } elseif (!empty($author)) {
            $fortuneArray = $fortune->getRandomFortuneByAuthor($author);
        } else {
            $fortuneArray = $fortune->getRandomFortune();
        }

        return $this->output($output, $fortuneArray);
    }

    private function output(OutputInterface $output, $fortuneArray)
    {
        $wordwrap = $this->getWordwrap();

        $quote  = $fortuneArray[0];
        $author = sprintf('    — %s', $fortuneArray[1]);

        if ($wordwrap > self::WORDWRAP_DISABLED) {
            $quote  = wordwrap($quote, $wordwrap);
            $author = wordwrap($author, $wordwrap);
        }

        $lines = [
            sprintf('<fg=green;options=bold>"%s"</>', $quote),
            sprintf('<fg=magenta;options=bold>%s</>', $author),
        ];

        $output->writeln($lines);

        return $this;
    }

    private function getWordwrapDefault()
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

    private function getWordwrap()
    {
        return $this->wordwrap;
    }

    private function setWordwrap($wordwrap)
    {
        $this->wordwrap = (int) $wordwrap;

        return $this;
    }

    private function getLength()
    {
        return $this->length;
    }

    private function setLength($length)
    {
        $this->length = (int) $length;

        return $this;
    }

    private function getAuthor()
    {
        return $this->author;
    }

    private function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }
}
