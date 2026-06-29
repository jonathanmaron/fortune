<?php
declare(strict_types=1);

namespace AppTest\Fortune;

use App\Fortune\Fortune;
use AppTest\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class FortuneTest extends AbstractTestCase
{
    private const string UUID_ONE = '11111111-1111-1111-1111-111111111111';

    private const string UUID_TWO = '22222222-2222-2222-2222-222222222222';

    private const string UUID_THREE = '33333333-3333-3333-3333-333333333333';

    /**
     * Test that every stored fortune is returned when the data directory contains files.
     */
    public function testGetAllFortunesReturnsEveryStoredFortuneWhenDataExists(): void
    {
        $fortune = $this->createStandardEnvironment();

        $fortunes = $fortune->getAllFortunes();

        self::assertCount(3, $fortunes);
        self::assertSame(['Quote one', 'Alice'], $fortunes[self::UUID_ONE]);
    }

    /**
     * Test that an empty list is returned when the fortune directory contains no files.
     */
    public function testGetAllFortunesReturnsEmptyListWhenDirectoryIsEmpty(): void
    {
        $fortune = $this->createFortune($this->createTemporaryDirectory(), $this->createTemporaryDirectory());

        self::assertSame([], $fortune->getAllFortunes());
    }

    /**
     * Test that the shipped data directory loads into a populated list of fortunes.
     */
    public function testGetAllFortunesLoadsShippedDataWhenUsingTheApplicationPaths(): void
    {
        $fortune = $this->createFortune(APP_PATH_FORTUNE, APP_PATH_INDEX);

        self::assertNotEmpty($fortune->getAllFortunes());
    }

    /**
     * Test that the indexed lengths are returned as integers when the length index exists.
     */
    public function testGetAllLengthsReturnsIndexedLengthsWhenIndexExists(): void
    {
        $fortune = $this->createStandardEnvironment();

        self::assertEqualsCanonicalizing([5, 10, 15], $fortune->getAllLengths());
    }

    /**
     * Test that an empty list is returned when the length index file is missing.
     */
    public function testGetAllLengthsReturnsEmptyListWhenIndexIsMissing(): void
    {
        $fortune = $this->createFortune($this->createTemporaryDirectory(), $this->createTemporaryDirectory());

        self::assertSame([], $fortune->getAllLengths());
    }

    /**
     * Test that the indexed authors are returned as strings when the author index exists.
     */
    public function testGetAllAuthorsReturnsIndexedAuthorsWhenIndexExists(): void
    {
        $fortune = $this->createStandardEnvironment();

        self::assertEqualsCanonicalizing(['Alice', 'Bob'], $fortune->getAllAuthors());
    }

    /**
     * Test that an empty list is returned when the author index file is missing.
     */
    public function testGetAllAuthorsReturnsEmptyListWhenIndexIsMissing(): void
    {
        $fortune = $this->createFortune($this->createTemporaryDirectory(), $this->createTemporaryDirectory());

        self::assertSame([], $fortune->getAllAuthors());
    }

    /**
     * Test that a non-empty quote and author pair is returned when fortunes are available.
     */
    public function testGetRandomFortuneReturnsQuoteAndAuthorPairWhenDataExists(): void
    {
        $fortune = $this->createStandardEnvironment();

        $pair = $fortune->getRandomFortune();

        self::assertNotSame('', $pair[0]);
        self::assertNotSame('', $pair[1]);
    }

    /**
     * Test that the shortest available quotation is returned when lengths straddle the median.
     */
    public function testGetRandomShortFortuneReturnsShortQuotationWhenLengthsStraddleMedian(): void
    {
        $fortune = $this->createStandardEnvironment();

        self::assertSame(['Quote one', 'Alice'], $fortune->getRandomShortFortune());
    }

    /**
     * Test that the longest available quotation is returned when lengths straddle the median.
     */
    public function testGetRandomLongFortuneReturnsLongQuotationWhenLengthsStraddleMedian(): void
    {
        $fortune = $this->createStandardEnvironment();

        self::assertSame(['Quote three is the longest of them all', 'Alice'], $fortune->getRandomLongFortune());
    }

    /**
     * Test that the matching pair is returned when the requested length exists in the index.
     */
    public function testGetRandomFortuneByLengthReturnsPairWhenLengthExists(): void
    {
        $fortune = $this->createStandardEnvironment();

        self::assertSame(['Quote two medium', 'Bob'], $fortune->getRandomFortuneByLength(10));
    }

    /**
     * Test that an empty pair is returned when the length index file is missing.
     */
    public function testGetRandomFortuneByLengthReturnsEmptyPairWhenIndexIsMissing(): void
    {
        $fortune = $this->createFortune($this->createTemporaryDirectory(), $this->createTemporaryDirectory());

        self::assertSame(['', ''], $fortune->getRandomFortuneByLength(10));
    }

    /**
     * Test that an empty pair is returned when the requested length is absent from the index.
     */
    public function testGetRandomFortuneByLengthReturnsEmptyPairWhenLengthIsAbsent(): void
    {
        $fortune = $this->createStandardEnvironment();

        self::assertSame(['', ''], $fortune->getRandomFortuneByLength(99999));
    }

    /**
     * Test that an empty pair is returned when the matched reference contains empty identifiers.
     */
    public function testGetRandomFortuneByLengthReturnsEmptyPairWhenReferenceIsEmpty(): void
    {
        $fortunePath = $this->createTemporaryDirectory();
        $indexPath   = $this->createTemporaryDirectory();
        $this->writeIndexFile($indexPath, 'length', [
            5 => [['', '']],
        ]);

        $fortune = $this->createFortune($fortunePath, $indexPath);

        self::assertSame(['', ''], $fortune->getRandomFortuneByLength(5));
    }

    /**
     * Test that an empty pair is returned when the referenced fortune file cannot be read.
     */
    public function testGetRandomFortuneByLengthReturnsEmptyPairWhenReferencedFileIsMissing(): void
    {
        $fortunePath = $this->createTemporaryDirectory();
        $indexPath   = $this->createTemporaryDirectory();
        $this->writeIndexFile($indexPath, 'length', [
            5 => [['missing.php', self::UUID_ONE]],
        ]);

        $fortune = $this->createFortune($fortunePath, $indexPath);

        self::assertSame(['', ''], $fortune->getRandomFortuneByLength(5));
    }

    /**
     * Test that an empty pair is returned when the referenced UUID is absent from the fortune file.
     */
    public function testGetRandomFortuneByLengthReturnsEmptyPairWhenUuidIsAbsentFromFile(): void
    {
        $fortunePath = $this->createTemporaryDirectory();
        $indexPath   = $this->createTemporaryDirectory();
        $this->writeFortuneFile($fortunePath, 'a.php', [
            self::UUID_ONE => ['Quote one', 'Alice'],
        ]);
        $this->writeIndexFile($indexPath, 'length', [
            5 => [['a.php', self::UUID_TWO]],
        ]);

        $fortune = $this->createFortune($fortunePath, $indexPath);

        self::assertSame(['', ''], $fortune->getRandomFortuneByLength(5));
    }

    /**
     * Test that a quotation by the requested author is returned when the author exists.
     */
    public function testGetRandomFortuneByAuthorReturnsPairWhenAuthorExists(): void
    {
        $fortune = $this->createStandardEnvironment();

        $pair = $fortune->getRandomFortuneByAuthor('Alice');

        self::assertSame('Alice', $pair[1]);
        self::assertNotSame('', $pair[0]);
    }

    /**
     * Test that an empty pair is returned when the requested author is absent from the index.
     */
    public function testGetRandomFortuneByAuthorReturnsEmptyPairWhenAuthorIsAbsent(): void
    {
        $fortune = $this->createStandardEnvironment();

        self::assertSame(['', ''], $fortune->getRandomFortuneByAuthor('Nobody'));
    }

    /**
     * Test that a fortune filename is composed from the fortune path and the given file name.
     */
    public function testGetFilenameComposesPathFromFortuneDirectory(): void
    {
        $fortune = $this->createFortune('/data/fortune', '/data/index');

        self::assertSame('/data/fortune/abc.php', $fortune->getFilename('abc.php'));
    }

    /**
     * Test that an index filename is composed from the index path and the given index name.
     *
     * @param non-empty-string $index
     * @param non-empty-string $expected
     */
    #[DataProvider('provideIndexNames')]
    public function testGetIndexFilenameComposesPathFromIndexDirectory(string $index, string $expected): void
    {
        $fortune = $this->createFortune('/data/fortune', '/data/index');

        self::assertSame($expected, $fortune->getIndexFilename($index));
    }

    /**
     * @return array<string, array{index: non-empty-string, expected: non-empty-string}>
     */
    public static function provideIndexNames(): array
    {
        return [
            'length index' => [
                'index' => 'length',
                'expected' => '/data/index/length.php',
            ],
            'author index' => [
                'index' => 'author',
                'expected' => '/data/index/author.php',
            ],
        ];
    }

    /**
     * Test that the fortune and index path accessors return the values previously set.
     */
    public function testFortuneAndIndexPathAccessorsReturnTheConfiguredValues(): void
    {
        $fortune = new Fortune();

        self::assertSame($fortune, $fortune->setFortunePath('/data/fortune'));
        self::assertSame($fortune, $fortune->setIndexPath('/data/index'));
        self::assertSame('/data/fortune', $fortune->getFortunePath());
        self::assertSame('/data/index', $fortune->getIndexPath());
    }

    private function createStandardEnvironment(): Fortune
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

        return $this->createFortune($fortunePath, $indexPath);
    }
}
