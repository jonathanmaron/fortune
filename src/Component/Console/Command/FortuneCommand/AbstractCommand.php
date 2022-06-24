<?php
declare(strict_types=1);

namespace App\Component\Console\Command\FortuneCommand;

use App\Component\Console\Command\AbstractCommand as ParentCommand;
use App\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

abstract class AbstractCommand extends ParentCommand
{
    // <editor-fold desc="Class Constants">

    protected const TERMINAL_WIDTH    = 80;

    protected const WORDWRAP_DISABLED = 0;

    protected const WORDWRAP_MIN      = 5;

    protected const WAIT_MIN          = 0;

    protected const WAIT_MAX          = 60;

    // </editor-fold>

    // <editor-fold desc="Class Properties">

    protected int    $wordwrap = 0;

    protected int    $length   = 0;

    protected int    $wait     = 0;

    protected string $author   = '';

    protected bool   $short    = false;

    protected bool   $long     = false;

    // </editor-fold>

    // <editor-fold desc="Command Configuration">

    protected function configureCommand(): void
    {
        $this->setName('fortune');

        $this->setDescription('Unix-style fortune program that displays a random quotation.');

        $this->setHelp('@todo: The <info>command</info> command. Example: <info>command</info>.');
    }

    protected function configureWordwrap(): void
    {
        $name        = 'wordwrap';
        $shortcut    = 'w';
        $mode        = InputOption::VALUE_REQUIRED;
        $description = 'Wrap lines at the "width" th character. Disable with "0"';
        $default     = $this->getWordwrapDefault();

        $this->addOption($name, $shortcut, $mode, $description, $default);
    }

    protected function configureLength(): void
    {
        $name        = 'length';
        $shortcut    = 'i';
        $mode        = InputOption::VALUE_OPTIONAL;
        $description = 'Show quotations of length "length" only';
        $default     = '';

        $this->addOption($name, $shortcut, $mode, $description, $default);
    }

    protected function configureWait(): void
    {
        $name        = 'wait';
        $shortcut    = 'p';
        $mode        = InputOption::VALUE_OPTIONAL;
        $description = 'Wait for "wait" seconds before terminating';
        $default     = '';

        $this->addOption($name, $shortcut, $mode, $description, $default);
    }

    protected function configureAuthor(): void
    {
        $name        = 'author';
        $shortcut    = 'a';
        $mode        = InputOption::VALUE_OPTIONAL;
        $description = 'Show quotations from author "author" only';
        $default     = '';

        $this->addOption($name, $shortcut, $mode, $description, $default);
    }

    protected function configureShort(): void
    {
        $name        = 'short';
        $shortcut    = 's';
        $mode        = InputOption::VALUE_NONE;
        $description = 'Show short quotations only';
        $this->addOption($name, $shortcut, $mode, $description);
    }

    protected function configureLong(): void
    {
        $name        = 'long';
        $shortcut    = 'l';
        $mode        = InputOption::VALUE_NONE;
        $description = 'Show long quotations only';

        $this->addOption($name, $shortcut, $mode, $description);
    }

    // </editor-fold>

    // <editor-fold desc="Option Value Validation and Setting">

    protected function initializeWordwrap(InputInterface $input): void
    {
        $wordwrap = $input->getOption('wordwrap');
        assert(is_string($wordwrap));
        $wordwrap = trim($wordwrap);

        if (!ctype_digit($wordwrap)) {
            $format  = '--wordwrap must be an integer between %s and %s';
            $message = sprintf($format, self::WORDWRAP_MIN, $this->getWordwrapDefault());
            throw new InvalidArgumentException($message);
        }

        $wordwrap = (int) $wordwrap;

        if ($wordwrap > self::WORDWRAP_DISABLED) {

            $wordwrapDefault = (int) $this->getWordwrapDefault();

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
    }

    protected function initializeLength(InputInterface $input): void
    {
        $fortune = $this->getFortune();

        $length = $input->getOption('length');
        assert(is_string($length));
        $length = trim($length);

        if (strlen($length) > 0) {

            if (!ctype_digit($length)) {
                $message = '--length must be an integer';
                throw new InvalidArgumentException($message);
            }

            $lengths = array_map(function (int $int): string {
                return (string) $int;
            }, $fortune->getAllLengths());

            if (!in_array($length, $lengths, true)) {
                $message = '--length contains an invalid length';
                throw new InvalidArgumentException($message);
            }
        }

        $length = (int) $length;

        $this->setLength($length);
    }

    protected function initializeWait(InputInterface $input): void
    {
        $wait = $input->getOption('wait');
        assert(is_string($wait));
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
    }

    protected function initializeAuthor(InputInterface $input): void
    {
        $fortune = $this->getFortune();

        $author = $input->getOption('author');
        assert(is_string($author));
        $author = trim($author);

        if (strlen($author) > 0) {
            if (!in_array($author, $fortune->getAllAuthors(), true)) {
                $message = '--author contains an invalid author';
                throw new InvalidArgumentException($message);
            }
        }

        $this->setAuthor($author);
    }

    protected function initializeShort(InputInterface $input): void
    {
        $short = $input->getOption('short');
        assert(is_bool($short));
        $this->setShort($short);
    }

    protected function initializeLong(InputInterface $input): void
    {
        $long = $input->getOption('long');
        assert(is_bool($long));
        $this->setLong($long);
    }

    // </editor-fold>

    // <editor-fold desc="Option Getters & Setters">

    protected function getWordwrapDefault(): string
    {
        $terminal = new Terminal();

        $width = $terminal->getWidth();

        if ($width > 0) {
            $width--;
        } else {
            $width = self::TERMINAL_WIDTH;
        }

        return (string) $width;
    }

    protected function getWordwrap(): int
    {
        return $this->wordwrap;
    }

    protected function setWordwrap(int $wordwrap): self
    {
        $this->wordwrap = $wordwrap;

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
        $this->long = $long;

        return $this;
    }

    // </editor-fold>

    // <editor-fold desc="Render Formatted Quote">

    protected function render(OutputInterface $output, array $fortuneArray): int
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

        return 0;
    }

    // </editor-fold>
}
