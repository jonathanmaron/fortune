<?php
declare(strict_types=1);

namespace App\Component\Console;

use App\Component\Console\Command\Factory;
use App\Component\Console\Command\FortuneCommand\FortuneCommand;
use App\Component\Console\Command\ImportCommand\ImportCommand;
use App\Component\Console\Command\IndexCommand\IndexCommand;
use App\Component\Console\Command\PurgeCommand\PurgeCommand;
use App\Component\Console\Command\StatisticsCommand\StatisticsCommand;
use Symfony\Component\Console\Application as ParentApp;
use Symfony\Component\Console\Command\Command;

class App extends ParentApp
{
    private const COMMANDS
        = [
            FortuneCommand::class,
            ImportCommand::class,
            IndexCommand::class,
            PurgeCommand::class,
            StatisticsCommand::class,
        ];

    protected function getDefaultCommands(): array
    {
        return array_merge(parent::getDefaultCommands(), array_map(function (string $command): Command {
            return (new Factory())->__invoke(null, $command, []);
        }, self::COMMANDS));
    }
}
