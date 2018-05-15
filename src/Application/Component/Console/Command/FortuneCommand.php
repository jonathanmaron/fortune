<?php

namespace Application\Component\Console\Command;

use Application\Exception\InvalidArgumentException;
use Application\Exception\RuntimeException;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class FortuneCommand extends AbstractCommand
{
    private const TERMINAL_WIDTH    = 80;

    private const WORDWRAP_DISABLED = 0;

    private const WORDWRAP_MIN      = 5;

    use LockableTrait;

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

        return $this;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $wordwrap = $input->getOption('wordwrap');

        if (!$this->lock()) {
            $message = 'The script is already running in another process.';
            throw new RuntimeException($message);
        }

        if (!is_numeric($wordwrap)) {
            $format  = '--wordwrap must be a digit between %s and %s';
            $message = sprintf($format, self::WORDWRAP_MIN, $this->getWordwrapDefault());
            throw new InvalidArgumentException($message);
        }

        if ($wordwrap > self::WORDWRAP_DISABLED) {

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

        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wordwrap = $input->getOption('wordwrap');

        $fortune = $this->getFortune()->getRandomFortune();

        $quote  = $fortune[0];
        $author = sprintf('    — %s', $fortune[1]);

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
}
