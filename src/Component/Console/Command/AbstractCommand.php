<?php
declare(strict_types=1);

namespace App\Component\Console\Command;

use App\Fortune\Fortune;
use Symfony\Component\Console\Command\Command as ParentCommand;

abstract class AbstractCommand extends ParentCommand
{
    private Fortune $fortune;

    public function getFortune(): Fortune
    {
        return $this->fortune;
    }

    public function setFortune(Fortune $fortune): self
    {
        $this->fortune = $fortune;

        return $this;
    }
}
