<?php

namespace Application\Component\Console;

use Application\Component\Console\Command\CommandFactory;
use Application\Component\Console\Command\FortuneCommand;
use Application\Component\Console\Command\FortuneImportCommand;
use Application\Component\Console\Command\FortuneIndexCommand;
use Application\Component\Console\Command\FortuneStatisticsCommand;
use Symfony\Component\Console\Application as ParentApplication;

class Application extends ParentApplication
{
    protected function getDefaultCommands()
    {
        $ret = parent::getDefaultCommands();

        $commands = [
            FortuneCommand::class,
            FortuneImportCommand::class,
            FortuneIndexCommand::class,
            FortuneStatisticsCommand::class,
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
