<?php
declare(strict_types=1);

namespace AppTest\Component\Console\Command;

use App\Component\Console\Command\FortuneCommand\FortuneCommand;
use App\Fortune\Fortune;
use AppTest\AbstractTestCase;

final class AbstractCommandTest extends AbstractTestCase
{
    /**
     * Test that the fortune accessor returns the same instance that was previously set.
     */
    public function testFortuneAccessorReturnsTheConfiguredInstance(): void
    {
        $command = new FortuneCommand();
        $fortune = new Fortune();

        self::assertSame($command, $command->setFortune($fortune));
        self::assertSame($fortune, $command->getFortune());
    }
}
