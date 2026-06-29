<?php
declare(strict_types=1);

namespace AppTest\Fortune;

use App\Fortune\Fortune;
use PHPUnit\Framework\TestCase;

final class FortuneTest extends TestCase
{
    /**
     * Test that all fortunes are loaded from the data directory.
     */
    public function testGetAllFortunesReturnsPopulatedList(): void
    {
        self::assertNotEmpty($this->createFortune()->getAllFortunes());
    }

    /**
     * Test that the author index exposes the known authors.
     */
    public function testGetAllAuthorsReturnsPopulatedList(): void
    {
        $authors = $this->createFortune()
            ->getAllAuthors();

        self::assertNotEmpty($authors);
        self::assertContains('Buddha', $authors);
    }

    /**
     * Test that the length index is loaded.
     */
    public function testGetAllLengthsReturnsPopulatedList(): void
    {
        self::assertNotEmpty($this->createFortune()->getAllLengths());
    }

    /**
     * Test that a random fortune is returned as a non-empty quote/author pair.
     */
    public function testGetRandomFortuneReturnsQuoteAndAuthor(): void
    {
        $fortune = $this->createFortune()
            ->getRandomFortune();

        self::assertNotSame('', $fortune[0]);
        self::assertNotSame('', $fortune[1]);
    }

    /**
     * Test that a random short fortune resolves to a non-empty quotation.
     */
    public function testGetRandomShortFortuneReturnsQuotation(): void
    {
        self::assertNotSame('', $this->createFortune()->getRandomShortFortune()[0]);
    }

    /**
     * Test that a random long fortune resolves to a non-empty quotation.
     */
    public function testGetRandomLongFortuneReturnsQuotation(): void
    {
        self::assertNotSame('', $this->createFortune()->getRandomLongFortune()[0]);
    }

    /**
     * Test that filtering by a known author returns a quotation by that author.
     */
    public function testGetRandomFortuneByAuthorReturnsThatAuthor(): void
    {
        $fortune = $this->createFortune()
            ->getRandomFortuneByAuthor('Buddha');

        self::assertSame('Buddha', $fortune[1]);
        self::assertNotSame('', $fortune[0]);
    }

    private function createFortune(): Fortune
    {
        $fortune = new Fortune();
        $fortune->setFortunePath(APP_PATH_FORTUNE);
        $fortune->setIndexPath(APP_PATH_INDEX);

        return $fortune;
    }
}
