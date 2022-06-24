<?php
declare(strict_types=1);

namespace App\Component\Console\Command\StatisticsCommand;

use App\Component\Console\Command\AbstractCommand as ParentCommand;
use App\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCommand extends ParentCommand
{
    // <editor-fold desc="Class Constants">

    protected const LIMIT_MIN = 0;

    // </editor-fold>

    // <editor-fold desc="Class Properties">

    protected int $limit = 0;

    // </editor-fold>

    // <editor-fold desc="Command Configuration">

    protected function configureCommand(): void
    {
        $this->setName('statistics');

        $this->setDescription('Show statistics');

        $this->setHelp('@todo: The <info>command</info> command. Example: <info>command</info>.');
    }

    protected function configureLimit(): void
    {
        $name        = 'limit';
        $shortcut    = null;
        $mode        = InputOption::VALUE_OPTIONAL;
        $description = 'Show top "limit" rows only';
        $default     = '';

        $this->addOption($name, $shortcut, $mode, $description, $default);
    }

    // </editor-fold>

    // <editor-fold desc="Option Value Validation and Setting">

    protected function initializeLimit(InputInterface $input): void
    {
        $limit = $input->getOption('limit');
        assert(is_string($limit));
        $limit = trim($limit);

        if (strlen($limit) > 0) {
            if (!ctype_digit($limit)) {
                $message = '--limit must be an integer';
                throw new InvalidArgumentException($message);
            }
        }

        $limit = (int) $limit;

        $this->setLimit($limit);
    }

    // </editor-fold>

    // <editor-fold desc="Option Getters & Setters">

    protected function getLimit(): int
    {
        return $this->limit;
    }

    protected function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    // </editor-fold>
}
