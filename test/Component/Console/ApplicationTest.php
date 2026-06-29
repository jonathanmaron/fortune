<?php
declare(strict_types=1);

namespace AppTest\Component\Console;

use App\Component\Console\Application;
use PHPUnit\Framework\TestCase;

final class ApplicationTest extends TestCase
{
    /**
     * Test that the application registers every fortune console command.
     */
    public function testApplicationRegistersAllCommands(): void
    {
        $application = new Application();
        $application->setAutoExit(false);

        self::assertTrue($application->has('fortune'));
        self::assertTrue($application->has('import'));
        self::assertTrue($application->has('index'));
        self::assertTrue($application->has('purge'));
        self::assertTrue($application->has('statistics'));
    }
}
