<?php
declare(strict_types=1);

namespace AppTest;

use App\Component\Filesystem\Filesystem;
use App\Fortune\Fortune;
use Ctw\Temp\Temp;
use Override;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @var list<string>
     */
    private array $temporaryPaths = [];

    #[Override]
    protected function tearDown(): void
    {
        $filesystem = new Filesystem();

        foreach ($this->temporaryPaths as $temporaryPath) {
            if ($filesystem->exists($temporaryPath)) {
                $filesystem->remove($temporaryPath);
            }
        }

        $this->temporaryPaths = [];

        parent::tearDown();
    }

    protected function createTemporaryDirectory(): string
    {
        $path = (new Temp('fortune', 'fortune-test-' . bin2hex(random_bytes(8))))->createPath();

        $this->temporaryPaths[] = $path;

        return $path;
    }

    protected function createFortune(string $fortunePath, string $indexPath): Fortune
    {
        $fortune = new Fortune();
        $fortune->setFortunePath($fortunePath);
        $fortune->setIndexPath($indexPath);

        return $fortune;
    }

    /**
     * @param array<string, array{string, string}> $fortunes
     */
    protected function writeFortuneFile(string $directory, string $filename, array $fortunes): string
    {
        $pathname = sprintf('%s/%s', $directory, $filename);

        new Filesystem()
            ->arrayExportFile($pathname, $fortunes);

        return $pathname;
    }

    /**
     * @param array<int|string, list<array{string, string}>> $index
     */
    protected function writeIndexFile(string $directory, string $key, array $index): string
    {
        $pathname = sprintf('%s/%s.php', $directory, $key);

        new Filesystem()
            ->arrayExportFile($pathname, $index);

        return $pathname;
    }

    protected function writeTextFile(string $directory, string $filename, string $contents): string
    {
        $pathname = sprintf('%s/%s', $directory, $filename);

        new Filesystem()
            ->dumpFile($pathname, $contents);

        return $pathname;
    }
}
