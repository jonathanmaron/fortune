<?php
declare(strict_types=1);

namespace AppTest\Component\Console;

use App\Component\Console\Application;
use AppTest\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ApplicationTest extends AbstractTestCase
{
    /**
     * Test that each fortune console command is registered with the application.
     *
     * @param non-empty-string $commandName
     */
    #[DataProvider('provideCommandNames')]
    public function testGetDefaultCommandsRegistersEachConsoleCommand(string $commandName): void
    {
        $application = new Application();
        $application->setAutoExit(false);

        self::assertTrue($application->has($commandName));
    }

    /**
     * @return array<string, array{commandName: non-empty-string}>
     */
    public static function provideCommandNames(): array
    {
        return [
            'fortune command'    => [
                'commandName' => 'fortune',
            ],
            'import command'     => [
                'commandName' => 'import',
            ],
            'index command'      => [
                'commandName' => 'index',
            ],
            'purge command'      => [
                'commandName' => 'purge',
            ],
            'statistics command' => [
                'commandName' => 'statistics',
            ],
        ];
    }
}
