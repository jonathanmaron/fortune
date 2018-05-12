<?php

namespace Application\Component\Console;

use Application\Component\Console\Command\CommandFactory;
use Application\Component\Console\Command\FortuneCommand;
use Symfony\Component\Console\Application as ParentApplication;

class Application extends ParentApplication
{
    protected function getDefaultCommands()
    {
        $ret = parent::getDefaultCommands();

        $commands = [
            FortuneCommand::class,
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
