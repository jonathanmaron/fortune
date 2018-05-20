<?php

declare(strict_types=1);

namespace Application\Component\Console\Command\StatisticsCommand;

use Application\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StatisticsCommand extends AbstractCommand
{
    protected function configure(): self
    {
        $this->setName('statistics');

        $this->setDescription('Show statistics');

        $name        = 'limit';
        $shortcut    = null;
        $mode        = InputOption::VALUE_OPTIONAL;
        $description = 'Show top "limit" rows only';
        $default     = '';

        $this->addOption($name, $shortcut, $mode, $description, $default);

        return $this;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): self
    {
        $limit = (string) $input->getOption('limit');
        $limit = trim($limit);

        if (strlen($limit) > 0) {
            if (!is_numeric($limit)) {
                $message = '--limit must be a digit';
                throw new InvalidArgumentException($message);
            }
        }

        settype($limit, 'int');

        $this->setLimit($limit);

        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output): self
    {
        $fortune = $this->getFortune();
        $limit   = $this->getLimit();

        $rows = [];
        foreach ($fortune->getAllFortunes() as $fortuneArray) {
            $quote  = $fortuneArray[0];
            $author = $fortuneArray[1];
            if (!isset($rows[$author])) {
                $rows[$author] = [
                    $author,
                    0,
                    0,
                ];
            }
            $rows[$author][1] += 1;
            $rows[$author][2] += str_word_count($quote);
        }

        usort($rows, function ($a, $b) {
            return $b[1] <=> $a[1];
        });

        $count = count($rows);
        if ($limit > $count) {
            $format  = '--limit must be a less than %d';
            $message = sprintf($format, $count);
            throw new InvalidArgumentException($message);
        }

        if ($limit > self::LIMIT_MIN) {
            $rows = array_slice($rows, 0, $limit);
        }

        $table = new Table($output);
        $table->setHeaders(['Author', 'Quotes', 'Words']);
        $table->setRows($rows);
        $table->render();

        return $this;
    }
}
