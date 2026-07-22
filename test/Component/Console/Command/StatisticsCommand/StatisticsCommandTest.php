<?php
declare(strict_types=1);

namespace AppTest\Component\Console\Command\StatisticsCommand;

use App\Component\Console\Command\StatisticsCommand\StatisticsCommand;
use App\Exception\InvalidArgumentException;
use AppTest\AbstractTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Tests the statistics command, covering the author breakdown, limiting and validation.
 */
final class StatisticsCommandTest extends AbstractTestCase
{
    /**
     * Test that the author breakdown table is rendered for every author when no limit is provided.
     */
    public function testExecuteRendersAuthorStatisticsForEveryAuthorWhenNoLimitIsProvided(): void
    {
        $tester = $this->createCommandTester();

        $tester->execute([], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();

        $display = $tester->getDisplay();
        self::assertStringContainsString('Author', $display);
        self::assertStringContainsString('Quotes', $display);
        self::assertStringContainsString('Words', $display);
        self::assertStringContainsString('Alice', $display);
        self::assertStringContainsString('Bob', $display);
        self::assertStringContainsString('Carol', $display);
    }

    /**
     * Test that only the most prolific authors are rendered when a limit is provided.
     */
    public function testExecuteLimitsRowsToTheMostProlificAuthorsWhenLimitIsProvided(): void
    {
        $tester = $this->createCommandTester();

        $tester->execute([
            '--limit' => '2',
        ], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();

        $display = $tester->getDisplay();
        self::assertStringContainsString('Alice', $display);
        self::assertStringContainsString('Bob', $display);
        self::assertStringNotContainsString('Carol', $display);
    }

    /**
     * Test that a limit greater than the number of authors is rejected with an exception.
     */
    public function testExecuteThrowsInvalidArgumentExceptionWhenLimitExceedsAuthorCount(): void
    {
        $tester = $this->createCommandTester();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('--limit must be a less than 3');

        $tester->execute([
            '--limit' => '999',
        ], [
            'interactive' => false,
        ]);
    }

    /**
     * Test that a non-numeric limit value is rejected with an exception.
     */
    public function testInitializeThrowsInvalidArgumentExceptionWhenLimitIsNotNumeric(): void
    {
        $tester = $this->createCommandTester();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('--limit must be an integer');

        $tester->execute([
            '--limit' => 'abc',
        ], [
            'interactive' => false,
        ]);
    }

    /**
     * Creates a command tester backed by fortune fixtures for three authors.
     */
    private function createCommandTester(): CommandTester
    {
        $fortunePath = $this->createTemporaryDirectory();
        $indexPath   = $this->createTemporaryDirectory();

        $this->writeFortuneFile($fortunePath, 'a.php', [
            '11111111-1111-1111-1111-111111111111' => ['Alice quote one', 'Alice'],
            '22222222-2222-2222-2222-222222222222' => ['Alice quote two', 'Alice'],
            '33333333-3333-3333-3333-333333333333' => ['Alice quote three', 'Alice'],
            '44444444-4444-4444-4444-444444444444' => ['Bob quote one', 'Bob'],
            '55555555-5555-5555-5555-555555555555' => ['Bob quote two', 'Bob'],
            '66666666-6666-6666-6666-666666666666' => ['Carol quote one', 'Carol'],
        ]);

        $command = new StatisticsCommand();
        $command->setFortune($this->createFortune($fortunePath, $indexPath));

        return new CommandTester($command);
    }
}
