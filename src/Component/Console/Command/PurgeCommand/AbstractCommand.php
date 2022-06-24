<?php
declare(strict_types=1);

namespace App\Component\Console\Command\PurgeCommand;

use App\Component\Console\Command\AbstractCommand as ParentCommand;

abstract class AbstractCommand extends ParentCommand
{
    // <editor-fold desc="Command Configuration">

    protected function configureCommand(): void
    {
        $this->setName('purge');

        $this->setDescription('Purge the data directories');

        $this->setHelp('@todo: The <info>command</info> command. Example: <info>command</info>.');
    }

    // </editor-fold>
}
