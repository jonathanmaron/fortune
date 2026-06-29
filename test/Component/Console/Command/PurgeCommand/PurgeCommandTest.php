<?php
declare(strict_types=1);

namespace AppTest\Component\Console\Command\PurgeCommand;

use App\Component\Console\Command\PurgeCommand\PurgeCommand;
use AppTest\AbstractTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class PurgeCommandTest extends AbstractTestCase
{
    /**
     * Test that the data directories are purged and recreated when the user confirms.
     */
    public function testExecutePurgesAndRecreatesDirectoriesWhenUserConfirms(): void
    {
        $fortunePath = $this->createTemporaryDirectory();
        $indexPath   = $this->createTemporaryDirectory();

        $tester = $this->createCommandTester($fortunePath, $indexPath);
        $tester->setInputs(['yes']);
        $tester->execute([], [
            'interactive' => true,
        ]);

        $tester->assertCommandIsSuccessful();

        $display = $tester->getDisplay();
        self::assertStringContainsString('Purged', $display);
        self::assertStringContainsString($fortunePath, $display);
        self::assertDirectoryExists($fortunePath);
        self::assertDirectoryExists($indexPath);
    }

    /**
     * Test that nothing is purged when the user declines the confirmation prompt.
     */
    public function testExecuteAbortsWithoutPurgingWhenUserDeclines(): void
    {
        $fortunePath = $this->createTemporaryDirectory();
        $indexPath   = $this->createTemporaryDirectory();

        $tester = $this->createCommandTester($fortunePath, $indexPath);
        $tester->setInputs(['no']);
        $tester->execute([], [
            'interactive' => true,
        ]);

        $tester->assertCommandIsSuccessful();

        $display = $tester->getDisplay();
        self::assertStringContainsString('You are about to purge:', $display);
        self::assertStringNotContainsString('Purged', $display);
    }

    private function createCommandTester(string $fortunePath, string $indexPath): CommandTester
    {
        $command = new PurgeCommand();
        $command->setFortune($this->createFortune($fortunePath, $indexPath));

        $application = new Application();
        $application->addCommand($command);

        return new CommandTester($command);
    }
}
