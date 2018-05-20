<?php

declare(strict_types=1);

namespace Application\Component\Console\Command;

use Application\Fortune\Fortune;
use Symfony\Component\Console\Command\Command as ParentCommand;

abstract class AbstractCommand extends ParentCommand
{
    private $fortune;

    public function getFortune()
    {
        return $this->fortune;
    }

    public function setFortune(Fortune $fortune)
    {
        $this->fortune = $fortune;

        return $this;
    }
}
