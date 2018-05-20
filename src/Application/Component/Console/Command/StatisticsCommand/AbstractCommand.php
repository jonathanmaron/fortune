<?php

namespace Application\Component\Console\Command\StatisticsCommand;

use Application\Component\Console\Command\AbstractCommand as ParentCommand;

abstract class AbstractCommand extends ParentCommand
{
    protected const LIMIT_MIN = 0;

    protected $limit;

    protected function getLimit()
    {
        return $this->limit;
    }

    protected function setLimit($limit)
    {
        $this->limit = (int) $limit;

        return $this;
    }
}
