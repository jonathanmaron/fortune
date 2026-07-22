<?php
declare(strict_types=1);

namespace AppTest\Component\Console\Command\FortuneCommand;

use App\Component\Console\Command\FortuneCommand\FortuneCommand;
use App\Exception\InvalidArgumentException;
use AppTest\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Tests the fortune command, covering filtering options and validation of invalid input.
 */
final class FortuneCommandTest extends AbstractTestCase
{
    /**
     * Fixture UUID for the first stored fortune.
     */
    private const string UUID_ONE = '11111111-1111-1111-1111-111111111111';

    /**
     * Fixture UUID for the second stored fortune.
     */
    private const string UUID_TWO = '22222222-2222-2222-2222-222222222222';

    /**
     * Fixture UUID for the third stored fortune.
     */
    private const string UUID_THREE = '33333333-3333-3333-3333-333333333333';

    /**
     * Test that a quotation is displayed when no filtering options are supplied.
     */
    public function testExecuteDisplaysQuotationWhenNoFiltersAreApplied(): void
    {
        $tester = $this->createCommandTester();

        $tester->execute([], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();
        self::assertNotSame('', trim($tester->getDisplay()));
    }

    /**
     * Test that the shortest quotation is displayed when the short option is enabled.
     */
    public function testExecuteDisplaysShortQuotationWhenShortOptionIsEnabled(): void
    {
        $tester = $this->createCommandTester();

        $tester->execute([
            '--short' => true,
        ], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('Quote one', $tester->getDisplay());
    }

    /**
     * Test that the longest quotation is displayed when the long option is enabled.
     */
    public function testExecuteDisplaysLongQuotationWhenLongOptionIsEnabled(): void
    {
        $tester = $this->createCommandTester();

        $tester->execute([
            '--long' => true,
        ], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('Quote three is the longest of them all', $tester->getDisplay());
    }

    /**
     * Test that a quotation of the requested length is displayed when the length option is provided.
     */
    public function testExecuteDisplaysQuotationOfRequestedLengthWhenLengthOptionIsProvided(): void
    {
        $tester = $this->createCommandTester();

        $tester->execute([
            '--length' => '10',
        ], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('Quote two medium', $tester->getDisplay());
    }

    /**
     * Test that a quotation by the requested author is displayed when the author option is provided.
     */
    public function testExecuteDisplaysQuotationByRequestedAuthorWhenAuthorOptionIsProvided(): void
    {
        $tester = $this->createCommandTester();

        $tester->execute([
            '--author' => 'Bob',
        ], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('Quote two medium', $tester->getDisplay());
    }

    /**
     * Test that output is produced when word wrapping is requested with a positive width.
     */
    public function testExecuteWrapsOutputWhenWordwrapOptionIsPositive(): void
    {
        $tester = $this->createCommandTester();

        $tester->execute([
            '--wordwrap' => '40',
        ], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();
        self::assertNotSame('', trim($tester->getDisplay()));
    }

    /**
     * Test that output is produced when word wrapping is disabled with a width of zero.
     */
    public function testExecuteDoesNotWrapOutputWhenWordwrapOptionIsZero(): void
    {
        $tester = $this->createCommandTester();

        $tester->execute([
            '--wordwrap' => '0',
        ], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();
        self::assertNotSame('', trim($tester->getDisplay()));
    }

    /**
     * Test that an oversized word wrap width is clamped to the default width.
     */
    public function testExecuteClampsWordwrapToDefaultWhenValueExceedsDefault(): void
    {
        $tester = $this->createCommandTester();

        $tester->execute([
            '--wordwrap' => '9999',
        ], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();
        self::assertNotSame('', trim($tester->getDisplay()));
    }

    /**
     * Test that the command waits before terminating when a positive wait option is provided.
     */
    public function testExecutePausesBeforeTerminatingWhenWaitOptionIsPositive(): void
    {
        $tester = $this->createCommandTester();

        $tester->execute([
            '--wait' => '1',
        ], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();
        self::assertNotSame('', trim($tester->getDisplay()));
    }

    /**
     * Test that the fallback terminal width is used when the COLUMNS environment variable is zero.
     */
    public function testExecuteUsesFallbackWidthWhenColumnsEnvironmentVariableIsZero(): void
    {
        $tester   = $this->createCommandTester();
        $previous = getenv('COLUMNS');

        putenv('COLUMNS=0');

        try {
            $tester->execute([], [
                'interactive' => false,
            ]);
            $tester->assertCommandIsSuccessful();
        } finally {
            if (false === $previous) {
                putenv('COLUMNS');
            } else {
                putenv(sprintf('COLUMNS=%s', $previous));
            }
        }

        self::assertNotSame('', trim($tester->getDisplay()));
    }

    /**
     * Test that an invalid option value is rejected with an explanatory exception.
     *
     * @param non-empty-string $option
     */
    #[DataProvider('provideInvalidOptions')]
    public function testInitializeRejectsInvalidOptionValues(string $option, string $value, string $message): void
    {
        $tester = $this->createCommandTester();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $tester->execute([
            $option => $value,
        ], [
            'interactive' => false,
        ]);
    }

    /**
     * Provides invalid option values paired with their expected exception messages.
     *
     * @return array<string, array{option: non-empty-string, value: string, message: string}>
     */
    public static function provideInvalidOptions(): array
    {
        return [
            'word wrap below minimum' => [
                'option'  => '--wordwrap',
                'value'   => '3',
                'message' => '--wordwrap must be greater than or equal to 5',
            ],
            'length not numeric'      => [
                'option'  => '--length',
                'value'   => 'abc',
                'message' => '--length must be an integer',
            ],
            'length absent from index' => [
                'option'  => '--length',
                'value'   => '99999',
                'message' => '--length contains an invalid length',
            ],
            'wait not numeric'        => [
                'option'  => '--wait',
                'value'   => 'xyz',
                'message' => '--wait must be an integer',
            ],
            'wait above maximum'      => [
                'option'  => '--wait',
                'value'   => '99',
                'message' => '--wait must be in range 0 to 60',
            ],
            'author absent from index' => [
                'option'  => '--author',
                'value'   => 'Nobody',
                'message' => '--author contains an invalid author',
            ],
        ];
    }

    /**
     * Test that a non-numeric word wrap value is rejected with an explanatory exception.
     */
    public function testInitializeRejectsNonNumericWordwrapValue(): void
    {
        $tester = $this->createCommandTester();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^--wordwrap must be an integer between 5 and \d+$/');

        $tester->execute([
            '--wordwrap' => 'abc',
        ], [
            'interactive' => false,
        ]);
    }

    /**
     * Creates a command tester backed by fortune, length and author index fixtures.
     */
    private function createCommandTester(): CommandTester
    {
        $fortunePath = $this->createTemporaryDirectory();
        $indexPath   = $this->createTemporaryDirectory();

        $this->writeFortuneFile($fortunePath, 'a.php', [
            self::UUID_ONE   => ['Quote one', 'Alice'],
            self::UUID_TWO   => ['Quote two medium', 'Bob'],
            self::UUID_THREE => ['Quote three is the longest of them all', 'Alice'],
        ]);

        $this->writeIndexFile($indexPath, 'length', [
            5  => [['a.php', self::UUID_ONE]],
            10 => [['a.php', self::UUID_TWO]],
            15 => [['a.php', self::UUID_THREE]],
        ]);

        $this->writeIndexFile($indexPath, 'author', [
            'Alice' => [['a.php', self::UUID_ONE], ['a.php', self::UUID_THREE]],
            'Bob'   => [['a.php', self::UUID_TWO]],
        ]);

        $command = new FortuneCommand();
        $command->setFortune($this->createFortune($fortunePath, $indexPath));

        return new CommandTester($command);
    }
}
