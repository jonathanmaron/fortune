<?php
declare(strict_types=1);

namespace AppTest\Component\Filesystem;

use App\Component\Filesystem\Filesystem;
use AppTest\AbstractTestCase;

final class FilesystemTest extends AbstractTestCase
{
    /**
     * Test that a single data array is exported to a readable, includable PHP file.
     */
    public function testArrayExportFileWritesAnIncludablePhpFileAndReturnsTrue(): void
    {
        $directory = $this->createTemporaryDirectory();
        $filename  = sprintf('%s/data.php', $directory);
        $data      = [
            '11111111-1111-1111-1111-111111111111' => ['Quote', 'Author'],
        ];

        $result = (new Filesystem())->arrayExportFile($filename, $data);

        self::assertTrue($result);
        self::assertFileExists($filename);

        $loaded = include $filename;

        self::assertSame($data, $loaded);
    }

    /**
     * Test that each chunk is exported to its own UUID-named file when chunks are provided.
     */
    public function testArrayExportFilesWritesOneFilePerChunkAndReturnsTrue(): void
    {
        $directory = $this->createTemporaryDirectory();

        $chunks = [
            [
                '11111111-1111-1111-1111-111111111111' => ['Quote one', 'Alice'],
            ],
            [
                '22222222-2222-2222-2222-222222222222' => ['Quote two', 'Bob'],
            ],
        ];

        $result = (new Filesystem())->arrayExportFiles($directory, $chunks);

        self::assertTrue($result);

        $files = glob(sprintf('%s/*.php', $directory));
        assert(is_array($files));

        self::assertCount(2, $files);
    }

    /**
     * Test that no files are written when an empty list of chunks is provided.
     */
    public function testArrayExportFilesWritesNothingWhenChunkListIsEmpty(): void
    {
        $directory = $this->createTemporaryDirectory();

        $result = (new Filesystem())->arrayExportFiles($directory, []);

        self::assertTrue($result);

        $files = glob(sprintf('%s/*.php', $directory));
        assert(is_array($files));

        self::assertCount(0, $files);
    }
}
