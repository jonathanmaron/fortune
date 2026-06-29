<?php
declare(strict_types=1);

namespace AppTest\Component\Console\Command\StatisticsCommand;

use App\Component\Console\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

final class StatisticsCommandTest extends TestCase
{
    /**
     * Test that the statistics command renders an author breakdown table.
     */
    public function testStatisticsCommandRendersTable(): void
    {
        $tester = $this->createTester();

        $tester->run([
            'command' => 'statistics',
            '--limit' => '5',
        ], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();

        $display = $tester->getDisplay();
        self::assertStringContainsString('Author', $display);
        self::assertStringContainsString('Quotes', $display);
        self::assertStringContainsString('Words', $display);
    }

    private function createTester(): ApplicationTester
    {
        $application = new Application();
        $application->setAutoExit(false);

        return new ApplicationTester($application);
    }
}
