<?php
declare(strict_types=1);

namespace AppTest\Component\Console\Command\ImportCommand;

use App\Component\Console\Command\ImportCommand\ImportCommand;
use App\Exception\InvalidArgumentException;
use App\Exception\RuntimeException;
use AppTest\AbstractTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class ImportCommandTest extends AbstractTestCase
{
    /**
     * Test that several new fortunes are imported and reported with a pluralized count.
     */
    public function testExecuteImportsNewFortunesAndReportsPluralCount(): void
    {
        $fortunePath = $this->createTemporaryDirectory();
        $jsonPath    = $this->createTemporaryDirectory();
        $this->writeTextFile(
            $jsonPath,
            'quotes.json',
            '[{"quoteText":"First imported quote","quoteAuthor":"Alice"},'
            . '{"quoteText":"Second imported quote","quoteAuthor":"Bob"}]'
        );

        $tester = $this->createCommandTester($fortunePath);
        $tester->execute([
            '--path' => $jsonPath,
        ], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('Added 2 fortunes.', $tester->getDisplay());
        self::assertCount(2, $this->loadImportedFortunes($fortunePath));
    }

    /**
     * Test that a single new fortune is imported and reported with a singular count.
     */
    public function testExecuteImportsSingleFortuneAndReportsSingularCount(): void
    {
        $fortunePath = $this->createTemporaryDirectory();
        $jsonPath    = $this->createTemporaryDirectory();
        $this->writeTextFile(
            $jsonPath,
            'quotes.json',
            '[{"quoteText":"Only imported quote","quoteAuthor":"Solo"}]'
        );

        $tester = $this->createCommandTester($fortunePath);
        $tester->execute([
            '--path' => $jsonPath,
        ], [
            'interactive' => false,
        ]);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('Added 1 fortune.', $tester->getDisplay());
    }

    /**
     * Test that fortunes already present in the database are skipped on a subsequent import.
     */
    public function testExecuteSkipsFortunesThatAreAlreadyPresentOnSubsequentImport(): void
    {
        $fortunePath = $this->createTemporaryDirectory();
        $jsonPath    = $this->createTemporaryDirectory();
        $this->writeTextFile(
            $jsonPath,
            'quotes.json',
            '[{"quoteText":"First imported quote","quoteAuthor":"Alice"},'
            . '{"quoteText":"Second imported quote","quoteAuthor":"Bob"}]'
        );

        $tester = $this->createCommandTester($fortunePath);
        $tester->execute([
            '--path' => $jsonPath,
        ], [
            'interactive' => false,
        ]);
        $tester->assertCommandIsSuccessful();

        $tester->execute([
            '--path' => $jsonPath,
        ], [
            'interactive' => false,
        ]);
        $tester->assertCommandIsSuccessful();

        self::assertStringContainsString('Added 0 fortunes.', $tester->getDisplay());
        self::assertCount(2, $this->loadImportedFortunes($fortunePath));
    }

    /**
     * Test that authors are normalized and invalid or duplicate quotations are filtered out.
     */
    public function testExecuteNormalizesAuthorsAndFiltersInvalidQuotations(): void
    {
        $fortunePath = $this->createTemporaryDirectory();
        $jsonPath    = $this->createTemporaryDirectory();
        $this->writeTextFile(
            $jsonPath,
            'quotes.json',
            '[{"quoteText":"Don’t worry","quoteAuthor":""},'
            . '{"quoteText":"","quoteAuthor":"Nobody"},'
            . '{"quoteText":"Unique quote one","quoteAuthor":"Zed"},'
            . '{"quoteText":"Unique quote one","quoteAuthor":"Dup"}]'
        );

        $tester = $this->createCommandTester($fortunePath);
        $tester->execute([
            '--path' => $jsonPath,
        ], [
            'interactive' => false,
        ]);
        $tester->assertCommandIsSuccessful();

        $fortunes = $this->loadImportedFortunes($fortunePath);

        $quotes  = [];
        $authors = [];
        foreach ($fortunes as [$quote, $author]) {
            $quotes[]  = $quote;
            $authors[] = $author;
        }

        self::assertCount(2, $fortunes);
        self::assertContains("Don't worry", $quotes);
        self::assertContains('Unknown', $authors);
        self::assertContains('Unique quote one', $quotes);
        self::assertContains('Zed', $authors);
        self::assertNotContains('Dup', $authors);
        self::assertNotContains('', $quotes);
    }

    /**
     * Test that an empty source results in a runtime exception because there is nothing to import.
     */
    public function testExecuteThrowsRuntimeExceptionWhenThereAreNoFortunesToProcess(): void
    {
        $fortunePath = $this->createTemporaryDirectory();
        $jsonPath    = $this->createTemporaryDirectory();
        $this->writeTextFile($jsonPath, 'quotes.json', '[]');

        $tester = $this->createCommandTester($fortunePath);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('There are no quotations to process');

        $tester->execute([
            '--path' => $jsonPath,
        ], [
            'interactive' => false,
        ]);
    }

    /**
     * Test that a non-existent source path is rejected with an exception.
     */
    public function testInitializeThrowsInvalidArgumentExceptionWhenPathDoesNotExist(): void
    {
        $tester = $this->createCommandTester($this->createTemporaryDirectory());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('--path contains an invalid path');

        $tester->execute([
            '--path' => '/this/path/does/not/exist',
        ], [
            'interactive' => false,
        ]);
    }

    private function createCommandTester(string $fortunePath): CommandTester
    {
        $command = new ImportCommand();
        $command->setFortune($this->createFortune($fortunePath, $this->createTemporaryDirectory()));

        return new CommandTester($command);
    }

    /**
     * @return array<string, array{string, string}>
     */
    private function loadImportedFortunes(string $directory): array
    {
        $files = glob(sprintf('%s/*.php', $directory));
        assert(is_array($files));

        $fortunes = [];
        foreach ($files as $file) {
            $data = include $file;
            assert(is_array($data));
            foreach ($data as $uuid => $pair) {
                assert(is_string($uuid));
                assert(is_array($pair));
                $quote  = $pair[0];
                $author = $pair[1];
                assert(is_string($quote));
                assert(is_string($author));
                $fortunes[$uuid] = [$quote, $author];
            }
        }

        return $fortunes;
    }
}
