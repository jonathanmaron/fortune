<?php
declare(strict_types=1);

namespace AppTest\Component\Console\Command;

use App\Component\Console\Command\CommandFactory;
use App\Component\Console\Command\FortuneCommand\FortuneCommand;
use AppTest\AbstractTestCase;

/**
 * Tests that the command factory instantiates and configures fortune commands.
 */
final class CommandFactoryTest extends AbstractTestCase
{
    /**
     * Test that the requested command is instantiated and returned.
     */
    public function testInvokeReturnsAnInstanceOfTheRequestedCommand(): void
    {
        $command = new CommandFactory()
            ->__invoke(null, FortuneCommand::class);

        self::assertInstanceOf(FortuneCommand::class, $command);
    }

    /**
     * Test that the returned command is configured with a fortune pointing at the application paths.
     */
    public function testInvokeConfiguresTheCommandWithTheApplicationFortuneAndIndexPaths(): void
    {
        $command = new CommandFactory()
            ->__invoke(null, FortuneCommand::class);

        self::assertInstanceOf(FortuneCommand::class, $command);
        self::assertSame(APP_PATH_FORTUNE, $command->getFortune()->getFortunePath());
        self::assertSame(APP_PATH_INDEX, $command->getFortune()->getIndexPath());
    }
}
