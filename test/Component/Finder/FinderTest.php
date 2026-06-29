<?php
declare(strict_types=1);

namespace AppTest\Component\Finder;

use App\Component\Finder\Finder;
use AppTest\AbstractTestCase;

final class FinderTest extends AbstractTestCase
{
    /**
     * Test that only files with a PHP extension are matched within the given directory.
     */
    public function testPhpMatchesOnlyPhpFilesWithinTheDirectory(): void
    {
        $directory = $this->createTemporaryDirectory();
        $this->writeTextFile($directory, 'one.php', '<?php');
        $this->writeTextFile($directory, 'two.php', '<?php');
        $this->writeTextFile($directory, 'three.json', '[]');
        $this->writeTextFile($directory, 'four.txt', 'text');

        $finder = new Finder();

        self::assertCount(2, $finder->php($directory));
    }

    /**
     * Test that only files with a JSON extension are matched within the given directory.
     */
    public function testJsonMatchesOnlyJsonFilesWithinTheDirectory(): void
    {
        $directory = $this->createTemporaryDirectory();
        $this->writeTextFile($directory, 'one.json', '[]');
        $this->writeTextFile($directory, 'two.php', '<?php');
        $this->writeTextFile($directory, 'three.txt', 'text');

        $finder = new Finder();

        self::assertCount(1, $finder->json($directory));
    }

    /**
     * Test that no files are matched when the directory contains no relevant files.
     */
    public function testPhpMatchesNothingWhenDirectoryIsEmpty(): void
    {
        $directory = $this->createTemporaryDirectory();

        $finder = new Finder();

        self::assertCount(0, $finder->php($directory));
    }
}
