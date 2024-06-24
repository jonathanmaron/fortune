<?php
declare(strict_types=1);

namespace App\Component\Console;

use App\Component\Console\Command\CommandFactory;
use App\Component\Console\Command\FortuneCommand\FortuneCommand;
use App\Component\Console\Command\ImportCommand\ImportCommand;
use App\Component\Console\Command\IndexCommand\IndexCommand;
use App\Component\Console\Command\PurgeCommand\PurgeCommand;
use App\Component\Console\Command\StatisticsCommand\StatisticsCommand;
use Override;
use Symfony\Component\Console\Application as ParentApplication;
use Symfony\Component\Console\Command\Command;

class Application extends ParentApplication
{
    private const array COMMANDS
        = [
            FortuneCommand::class,
            ImportCommand::class,
            IndexCommand::class,
            PurgeCommand::class,
            StatisticsCommand::class,
        ];

    #[Override]
    protected function getDefaultCommands(): array
    {
        return array_merge(
            parent::getDefaultCommands(),
            array_map(fn(string $command): Command => (new CommandFactory())->__invoke(null, $command), self::COMMANDS)
        );
    }
}
