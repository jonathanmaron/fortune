<?php
declare(strict_types=1);

namespace App\Component\Console\Command\StatisticsCommand;

use App\Exception\InvalidArgumentException;
use Override;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatisticsCommand extends AbstractCommand
{
    #[Override]
    protected function configure(): void
    {
        $this->configureCommand();
        $this->configureLimit();
    }

    #[Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->initializeLimit($input);
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fortune = $this->getFortune();
        $limit   = $this->getLimit();

        $rows = [];
        foreach ($fortune->getAllFortunes() as $fortuneArray) {
            $quote  = $fortuneArray[0];
            $author = $fortuneArray[1];
            if (!isset($rows[$author])) {
                $rows[$author] = [$author, 0, 0];
            }
            ++$rows[$author][1];
            $rows[$author][2] += str_word_count($quote);
        }

        usort($rows, fn($a, $b) => $b[1] <=> $a[1]);

        $count = count($rows);
        if ($limit > $count) {
            $format  = '--limit must be a less than %d';
            $message = sprintf($format, $count);
            throw new InvalidArgumentException($message);
        }

        if (self::LIMIT_MIN < $limit) {
            $rows = array_slice($rows, 0, $limit);
        }

        $table = new Table($output);
        $table->setHeaders(['Author', 'Quotes', 'Words']);
        $table->setRows($rows);
        $table->render();

        return 0;
    }
}
