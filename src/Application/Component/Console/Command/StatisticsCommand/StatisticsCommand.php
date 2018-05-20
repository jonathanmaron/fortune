<?php

namespace Application\Component\Console\Command\StatisticsCommand;

use Application\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StatisticsCommand extends AbstractCommand
{

    protected function configure()
    {
        $this->setName('statistics');

        $this->setDescription('Show statistics');

        $name        = 'limit';
        $shortcut    = null;
        $mode        = InputOption::VALUE_OPTIONAL;
        $description = 'Limit table to top <limit>';
        $default     = null;

        $this->addOption($name, $shortcut, $mode, $description, $default);

        return $this;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $limit = $input->getOption('limit');
        $limit = trim($limit);

        if (!empty($limit)) {
            if (!is_numeric($limit)) {
                $message = '--limit must be a digit';
                throw new InvalidArgumentException($message);
            }
        }

        $this->setLimit($limit);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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

        usort($rows, function ($v1, $v2) {
            return $v2[1] <=> $v1[1];
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
