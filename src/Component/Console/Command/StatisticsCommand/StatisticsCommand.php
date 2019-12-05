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
    protected function configure(): void
    {
        $this->setName('statistics');

        $this->setDescription('Show statistics');

        $this->setHelp('@todo: The <info>command</info> command. Example: <info>command</info>.');

        // <editor-fold desc="InputOption: (int) limit">

        $name        = 'limit';
        $shortcut    = null;
        $mode        = InputOption::VALUE_OPTIONAL;
        $description = 'Show top "limit" rows only';
        $default     = '';

        $this->addOption($name, $shortcut, $mode, $description, $default);

        // </editor-fold>

        return;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // <editor-fold desc="InputOption: (int) limit">

        $limit = (string) $input->getOption('limit');
        $limit = trim($limit);

        if (strlen($limit) > 0) {
            if (!ctype_digit($limit)) {
                $message = '--limit must be an integer';
                throw new InvalidArgumentException($message);
            }
        }

        $limit = (int) $limit;

        $this->setLimit($limit);

        // </editor-fold>

        return;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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

        return 0;
    }
}
