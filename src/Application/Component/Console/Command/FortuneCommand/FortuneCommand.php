<?php

declare(strict_types=1);

namespace Application\Component\Console\Command\FortuneCommand;

use Application\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FortuneCommand extends AbstractCommand
{
    protected function configure(): self
    {
        $this->setName('fortune');

        $this->setDescription('Unix-style fortune program that displays a random quotation.');

        $name        = 'wordwrap';
        $shortcut    = 'w';
        $mode        = InputOption::VALUE_REQUIRED;
        $description = 'Wrap lines at the "w" th character. Default is terminal width. Disable with "0"';
        $default     = $this->getWordwrapDefault();

        $this->addOption($name, $shortcut, $mode, $description, $default);

        $name        = 'length';
        $shortcut    = 'i';
        $mode        = InputOption::VALUE_OPTIONAL;
        $description = 'Show quotations of length "length" only';
        $default     = '';

        $this->addOption($name, $shortcut, $mode, $description, $default);

        $name        = 'author';
        $shortcut    = 'a';
        $mode        = InputOption::VALUE_OPTIONAL;
        $description = 'Show quotations from author "author" only';
        $default     = '';

        $this->addOption($name, $shortcut, $mode, $description, $default);

        $name        = 'short';
        $shortcut    = 's';
        $mode        = InputOption::VALUE_NONE;
        $description = 'Show short quotations only';

        $this->addOption($name, $shortcut, $mode, $description);

        $name        = 'long';
        $shortcut    = 'l';
        $mode        = InputOption::VALUE_NONE;
        $description = 'Show long quotations only';

        $this->addOption($name, $shortcut, $mode, $description);

        return $this;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): self
    {
        $fortune = $this->getFortune();

        $wordwrap = (string) $input->getOption('wordwrap');
        $wordwrap = trim($wordwrap);

        if (!is_numeric($wordwrap)) {
            $format  = '--wordwrap must be a digit between %s and %s';
            $message = sprintf($format, self::WORDWRAP_MIN, $this->getWordwrapDefault());
            throw new InvalidArgumentException($message);
        }

        settype($wordwrap, 'int');

        if ($wordwrap > self::WORDWRAP_DISABLED) {

            $wordwrapDefault = $this->getWordwrapDefault();

            if ($wordwrap > $wordwrapDefault) {
                $wordwrap = $wordwrapDefault;
            }

            if ($wordwrap < self::WORDWRAP_MIN) {
                $format  = '--wordwrap must be greater than or equal to %d';
                $message = sprintf($format, self::WORDWRAP_MIN);
                throw new InvalidArgumentException($message);
            }

            if ($wordwrap > $this->getWordwrapDefault()) {
                $format  = '--wordwrap must be less than or equal to %d';
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

        settype($length, 'int');

        $this->setLength($length);

        $author = $input->getOption('author');
        $author = trim($author);

        if (!empty($author)) {
            if (!in_array($author, $fortune->getAllAuthors())) {
                $message = '--author contains an invalid author';
                throw new InvalidArgumentException($message);
            }
        }

        settype($author, 'string');

        $this->setAuthor($author);

        $short = $input->getOption('short');
        settype($short, 'boolean');
        $this->setShort($short);

        $long = $input->getOption('long');
        settype($long, 'boolean');
        $this->setLong($long);

        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output): self
    {
        $fortune = $this->getFortune();
        $short   = $this->getShort();
        $long    = $this->getLong();
        $length  = $this->getLength();
        $author  = $this->getAuthor();

        if ($short) {
            return $this->output($output, $fortune->getRandomShortFortune());
        }

        if ($long) {
            return $this->output($output, $fortune->getRandomLongFortune());
        }

        if ($length > 0) {
            return $this->output($output, $fortune->getRandomFortuneByLength($length));
        }

        if (strlen($author) > 0) {
            return $this->output($output, $fortune->getRandomFortuneByAuthor($author));
        }

        return $this->output($output, $fortune->getRandomFortune());
    }

    private function output(OutputInterface $output, array $fortuneArray): self
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
}
