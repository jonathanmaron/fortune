<?php
declare(strict_types=1);

namespace AppTest\Component\Console\Command\FortuneCommand;

use App\Component\Console\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

final class FortuneCommandTest extends TestCase
{
    /**
     * Test that the fortune command prints a quotation.
     */
    public function testFortuneCommandDisplaysQuotation(): void
    {
        $tester = $this->createTester();

        $tester->run([
            'command' => 'fortune',
        ], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();
        self::assertNotSame('', trim($tester->getDisplay()));
    }

    /**
     * Test that the fortune command honors the --short option.
     */
    public function testFortuneCommandSupportsShortOption(): void
    {
        $tester = $this->createTester();

        $tester->run([
            'command' => 'fortune',
            '--short' => true,
        ], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();
        self::assertNotSame('', trim($tester->getDisplay()));
    }

    private function createTester(): ApplicationTester
    {
        $application = new Application();
        $application->setAutoExit(false);

        return new ApplicationTester($application);
    }
}
