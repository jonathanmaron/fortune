<?php

declare(strict_types=1);

namespace Application\Component\Console;

use Application\Component\Console\Command\CommandFactory;
use Application\Component\Console\Command\FortuneCommand\FortuneCommand;
use Application\Component\Console\Command\ImportCommand\ImportCommand;
use Application\Component\Console\Command\IndexCommand\IndexCommand;
use Application\Component\Console\Command\StatisticsCommand\StatisticsCommand;
use Symfony\Component\Console\Application as ParentApplication;

class Application extends ParentApplication
{
    protected function getDefaultCommands()
    {
        $ret = parent::getDefaultCommands();

        $commands = [
            FortuneCommand::class,
            ImportCommand::class,
            IndexCommand::class,
            StatisticsCommand::class,
        ];

        $container = null;
        $options   = [];

        foreach ($commands as $requestedName) {
            $instance = new CommandFactory();
            $command  = $instance($container, $requestedName, $options);
            array_push($ret, $command);
        }

        return $ret;
    }
}
