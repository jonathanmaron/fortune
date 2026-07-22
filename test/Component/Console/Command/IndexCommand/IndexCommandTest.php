<?php
declare(strict_types=1);

namespace AppTest\Component\Console\Command\IndexCommand;

use App\Component\Console\Command\IndexCommand\IndexCommand;
use AppTest\AbstractTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class IndexCommandTest extends AbstractTestCase
{
    /**
     * Test that consumable length and author indexes are built from the stored fortune files.
     */
    public function testExecuteBuildsConsumableLengthAndAuthorIndexes(): void
    {
        $fortunePath = $this->createTemporaryDirectory();
        $indexPath   = $this->createTemporaryDirectory();

        $this->writeFortuneFile($fortunePath, 'a.php', [
            '11111111-1111-1111-1111-111111111111' => ['abc', 'Alice'],
            '22222222-2222-2222-2222-222222222222' => ['abcdef', 'Bob'],
        ]);

        $fortune = $this->createFortune($fortunePath, $indexPath);

        $command = new IndexCommand();
        $command->setFortune($fortune);

        $tester = new CommandTester($command);

        $tester->execute([], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();

        $display = $tester->getDisplay();
        self::assertStringContainsString('Wrote "length" index', $display);
        self::assertStringContainsString('Wrote "author" index', $display);

        self::assertEqualsCanonicalizing([3, 6], $fortune->getAllLengths());
        self::assertEqualsCanonicalizing(['Alice', 'Bob'], $fortune->getAllAuthors());
    }

    /**
     * Test that the index directory is created before indexes are written when it does not yet exist.
     */
    public function testExecuteCreatesIndexDirectoryWhenItDoesNotExist(): void
    {
        $fortunePath = $this->createTemporaryDirectory();
        $indexPath   = sprintf('%s/missing-index', $this->createTemporaryDirectory());

        self::assertDirectoryDoesNotExist($indexPath);

        $this->writeFortuneFile($fortunePath, 'a.php', [
            '11111111-1111-1111-1111-111111111111' => ['abc', 'Alice'],
        ]);

        $fortune = $this->createFortune($fortunePath, $indexPath);

        $command = new IndexCommand();
        $command->setFortune($fortune);

        $tester = new CommandTester($command);

        $tester->execute([], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();

        self::assertDirectoryExists($indexPath);
        self::assertEqualsCanonicalizing([3], $fortune->getAllLengths());
        self::assertEqualsCanonicalizing(['Alice'], $fortune->getAllAuthors());
    }

    /**
     * Test that a pre-existing index directory is purged and rebuilt so stale index files do not survive.
     */
    public function testExecuteRemovesStaleIndexDirectoryBeforeRebuildingIndexes(): void
    {
        $fortunePath = $this->createTemporaryDirectory();
        $indexPath   = $this->createTemporaryDirectory();

        $stalePathname = $this->writeTextFile($indexPath, 'stale.php', '<?php return [];');
        self::assertFileExists($stalePathname);

        $this->writeFortuneFile($fortunePath, 'a.php', [
            '11111111-1111-1111-1111-111111111111' => ['abc', 'Alice'],
        ]);

        $fortune = $this->createFortune($fortunePath, $indexPath);

        $command = new IndexCommand();
        $command->setFortune($fortune);

        $tester = new CommandTester($command);

        $tester->execute([], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();

        self::assertFileDoesNotExist($stalePathname);
        self::assertFileExists($fortune->getIndexFilename('length'));
        self::assertFileExists($fortune->getIndexFilename('author'));
    }
}
