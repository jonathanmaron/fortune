<?php
declare(strict_types=1);

namespace AppTest\PhpEncoder;

use App\PhpEncoder\PhpEncoder;
use AppTest\AbstractTestCase;

/**
 * Tests that the PHP encoder produces evaluable, multiline array source.
 */
final class PhpEncoderTest extends AbstractTestCase
{
    /**
     * Test that an array is encoded into PHP source that evaluates back to the original value.
     */
    public function testEncodeProducesSourceThatEvaluatesToTheOriginalArray(): void
    {
        $encoder = new PhpEncoder();

        $encoded = $encoder->encode([
            'key' => ['quote', 'author'],
        ]);

        self::assertStringContainsString("'quote'", $encoded);
        self::assertStringContainsString("'author'", $encoded);

        $evaluated = eval(sprintf('return %s;', $encoded));

        self::assertSame([
            'key' => ['quote', 'author'],
        ], $evaluated);
    }

    /**
     * Test that supplied constructor options are discarded in favor of the fixed configuration.
     */
    public function testConstructorIgnoresSuppliedOptionsAndUsesMultilineArrayFormatting(): void
    {
        $encoder = new PhpEncoder([
            'array.inline' => true,
        ]);

        $encoded = $encoder->encode(['a', 'b']);

        self::assertStringContainsString("\n", $encoded);
    }
}
