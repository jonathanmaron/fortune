<?php
declare(strict_types=1);

namespace Application\Component\Console;

use Application\Component\Console\Command\Factory;
use Application\Component\Console\Command\FortuneCommand\FortuneCommand;
use Application\Component\Console\Command\ImportCommand\ImportCommand;
use Application\Component\Console\Command\IndexCommand\IndexCommand;
use Application\Component\Console\Command\PurgeCommand\PurgeCommand;
use Application\Component\Console\Command\StatisticsCommand\StatisticsCommand;
use Symfony\Component\Console\Application as ParentApplication;

class Application extends ParentApplication
{
    protected function getDefaultCommands(): array
    {
        $ret = parent::getDefaultCommands();

        $commands = [
            FortuneCommand::class,
            ImportCommand::class,
            IndexCommand::class,
            StatisticsCommand::class,
            PurgeCommand::class,
        ];

        $container = null;
        $options   = [];

        foreach ($commands as $requestedName) {
            $instance = new Factory();
            $ret[]    = $instance($container, $requestedName, $options);
        }

        return $ret;
    }
}
