<?php
declare(strict_types=1);

namespace Application\Component\Console\Command\StatisticsCommand;

use Application\Component\Console\Command\AbstractCommand as ParentCommand;

abstract class AbstractCommand extends ParentCommand
{
    protected const LIMIT_MIN = 0;

    protected $limit;

    protected function getLimit(): int
    {
        return $this->limit;
    }

    protected function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }
}
