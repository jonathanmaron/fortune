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

        $this->setHelp('@todo: The <info>command</info> command. Example: <info>command</info>.');

        // <editor-fold desc="InputOption: (int) wordwrap">

        $name        = 'wordwrap';
        $shortcut    = 'w';
        $mode        = InputOption::VALUE_REQUIRED;
        $description = 'Wrap lines at the "width" th character. Disable with "0"';
        $default     = $this->getWordwrapDefault();

        $this->addOption($name, $shortcut, $mode, $description, $default);

        // </editor-fold>

        // <editor-fold desc="InputOption: (int) length">

        $name        = 'length';
        $shortcut    = 'i';
        $mode        = InputOption::VALUE_OPTIONAL;
        $description = 'Show quotations of length "length" only';
        $default     = '';

        $this->addOption($name, $shortcut, $mode, $description, $default);

        // </editor-fold>

        // <editor-fold desc="InputOption: (int) wait">

        $name        = 'wait';
        $shortcut    = 'p';
        $mode        = InputOption::VALUE_OPTIONAL;
        $description = 'Wait for "wait" seconds before terminating';
        $default     = '';

        $this->addOption($name, $shortcut, $mode, $description, $default);

        // </editor-fold>

        // <editor-fold desc="InputOption: (string) author">

        $name        = 'author';
        $shortcut    = 'a';
        $mode        = InputOption::VALUE_OPTIONAL;
        $description = 'Show quotations from author "author" only';
        $default     = '';

        $this->addOption($name, $shortcut, $mode, $description, $default);

        // </editor-fold>

        // <editor-fold desc="InputOption: (bool) short">

        $name        = 'short';
        $shortcut    = 's';
        $mode        = InputOption::VALUE_NONE;
        $description = 'Show short quotations only';

        $this->addOption($name, $shortcut, $mode, $description);

        // </editor-fold>

        // <editor-fold desc="InputOption: (bool) long">

        $name        = 'long';
        $shortcut    = 'l';
        $mode        = InputOption::VALUE_NONE;
        $description = 'Show long quotations only';

        $this->addOption($name, $shortcut, $mode, $description);

        // </editor-fold>

        return $this;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): self
    {
        $fortune = $this->getFortune();

        // <editor-fold desc="InputOption: (int) wordwrap">

        $wordwrap = (string) $input->getOption('wordwrap');
        $wordwrap = trim($wordwrap);

        if (!ctype_digit($wordwrap)) {
            $format  = '--wordwrap must be an integer between %s and %s';
            $message = sprintf($format, self::WORDWRAP_MIN, $this->getWordwrapDefault());
            throw new InvalidArgumentException($message);
        }

        $wordwrap = (int) $wordwrap;

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
        }

        $this->setWordwrap($wordwrap);

        // </editor-fold>

        // <editor-fold desc="InputOption: (int) length">

        $length = (string) $input->getOption('length');
        $length = trim($length);

        if (strlen($length) > 0) {

            if (!ctype_digit($length)) {
                $message = '--length must be an integer';
                throw new InvalidArgumentException($message);
            }

            if (!in_array($length, $fortune->getAllLengths())) {
                $message = '--length contains an invalid length';
                throw new InvalidArgumentException($message);
            }
        }

        $length = (int) $length;

        $this->setLength($length);

        // </editor-fold>

        // <editor-fold desc="InputOption: (int) wait">

        $wait = (string) $input->getOption('wait');
        $wait = trim($wait);

        if (strlen($wait) > 0) {
            if (!ctype_digit($wait)) {
                $message = '--wait must be an integer';
                throw new InvalidArgumentException($message);
            }
        }

        $wait = (int) $wait;

        if ($wait < self::WAIT_MIN || $wait > self::WAIT_MAX) {
            $format  = '--wait must be in range %d to %d';
            $message = sprintf($format, self::WAIT_MIN, self::WAIT_MAX);
            throw new InvalidArgumentException($message);
        }

        $this->setWait($wait);

        // </editor-fold>

        // <editor-fold desc="InputOption: (string) author">

        $author = (string) $input->getOption('author');
        $author = trim($author);

        if (strlen($author) > 0) {
            if (!in_array($author, $fortune->getAllAuthors())) {
                $message = '--author contains an invalid author';
                throw new InvalidArgumentException($message);
            }
        }

        $author = (string) $author;

        $this->setAuthor($author);

        // </editor-fold>

        // <editor-fold desc="InputOption: (bool) short">

        $short = (bool) $input->getOption('short');
        $this->setShort($short);

        // </editor-fold>

        // <editor-fold desc="InputOption: (bool) long">

        $long = (bool) $input->getOption('long');
        $this->setLong($long);

        // </editor-fold>

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
        $wait     = $this->getWait();

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

        if ($wait > 0) {
            sleep($wait);
        }

        return $this;
    }
}
