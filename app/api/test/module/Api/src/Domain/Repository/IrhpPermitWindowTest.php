<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use DateTime;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as Repo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow as Entity;

final class IrhpPermitWindowTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' ipw';

    private const string STOCK_JOINS = ' INNER JOIN ipw.irhpPermitStock ips'
        . ' INNER JOIN ips.irhpPermitType ipt';

    /** The two families of method name their type parameter differently. */
    private const string OPEN_WHERE = ' WHERE ipt.id = :type'
        . ' AND ipw.startDate <= :now AND ipw.endDate > :now';

    private const string OPEN_WHERE_BY_TYPE_ID = ' WHERE ipt.id = :irhpPermitTypeId'
        . ' AND ipw.startDate <= :now AND ipw.endDate > :now';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    /**
     * The parameters are on the left of both comparisons — the window is matched by the date
     * falling inside it, not the other way round.
     */
    public function testFetchOpenWindows(): void
    {
        $now = new DateTime('2019-01-01');

        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame(['RESULTS'], $this->sut->fetchOpenWindows(1, $now));

        $this->assertSame(
            // The outer andX is flattened into the WHERE; only the BETWEEN keeps brackets.
            'SELECT ipw' . self::FROM
            . ' WHERE ?1 = ipw.irhpPermitStock AND (?2 BETWEEN ipw.startDate AND ipw.endDate)',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter(1)->getValue());
        $this->assertSame($now, $qb->getParameter(2)->getValue());
    }

    public function testFetchByIrhpPermitStockId(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByIrhpPermitStockId(1));

        $this->assertSame(
            'SELECT ipw' . self::FROM . ' WHERE ipw.irhpPermitStock = :irhpPermitStock',
            $qb->getDQL(),
        );
    }

    public function testFetchLastOpenWindowByStockId(): void
    {
        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULT']);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame('RESULT', $this->sut->fetchLastOpenWindowByStockId(1));

        $this->assertSame(
            'SELECT ipw' . self::FROM
            . ' WHERE (?1 BETWEEN ipw.startDate AND ipw.endDate) AND ipw.irhpPermitStock = ?2'
            . ' ORDER BY ipw.id DESC',
            $qb->getDQL(),
        );
    }

    public function testFetchLastOpenWindowByStockIdNotFound(): void
    {
        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getResult')->andReturn([]);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchLastOpenWindowByStockId(1);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('lastOpenByTypeProvider')]
    public function testFetchLastOpenWindowByIrhpPermitType(?int $year, string $expectedExtra): void
    {
        $now = new DateTime('2019-06-01');

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULT']);

        $this->assertSame(
            'RESULT',
            $this->sut->fetchLastOpenWindowByIrhpPermitType(1, $now, Query::HYDRATE_OBJECT, $year),
        );

        $this->assertSame(
            'SELECT ipw' . self::FROM . self::STOCK_JOINS . self::OPEN_WHERE_BY_TYPE_ID . $expectedExtra
            . ' ORDER BY ipw.endDate DESC',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getMaxResults());
    }

    public static function lastOpenByTypeProvider(): \Iterator
    {
        yield 'any year' => [null, ''];
        // A BETWEEN nested inside an AND chain is bracketed.
        yield 'one year' => [2019, ' AND (ips.validTo BETWEEN :fromDate AND :toDate)'];
    }

    public function testFetchLastOpenWindowByIrhpPermitTypeNotFound(): void
    {
        $this->createRealQb()->stubbedQuery()->expects('getResult')->andReturn([]);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchLastOpenWindowByIrhpPermitType(1, new DateTime());
    }

    /**
     * The window is closed if its end date fell inside the period looked back over.
     */
    public function testFetchWindowsToBeClosed(): void
    {
        $now = new DateTime('2019-06-02 09:00:00');

        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchWindowsToBeClosed($now));

        $this->assertSame(
            'SELECT ipw' . self::FROM
            . ' WHERE ipw.endDate >= :periodStart AND ipw.endDate < :periodEnd',
            $qb->getDQL(),
        );
        $this->assertSame(
            '2019-06-01 00:00:00',
            $qb->getParameter('periodStart')->getValue()->format('Y-m-d H:i:s'),
        );
        $this->assertSame($now, $qb->getParameter('periodEnd')->getValue());
    }

    public function testFetchOpenWindowsByCountry(): void
    {
        $now = new DateTime('2019-01-01');

        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchOpenWindowsByCountry(1, ['FR', 'DE'], $now));

        $this->assertSame(
            'SELECT DISTINCT ipw' . self::FROM . self::STOCK_JOINS . ' INNER JOIN ips.country c'
            . self::OPEN_WHERE . ' AND c.id IN(:countries)',
            $qb->getDQL(),
        );
        $this->assertSame(['FR', 'DE'], $qb->getParameter('countries')->getValue());
    }

    /**
     * Stocks flagged hiddenSs are withheld from self-serve but visible internally.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('internalUserProvider')]
    public function testFetchOpenWindowsByType(bool $internalUser, string $expectedExtra): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchOpenWindowsByType(1, new DateTime('2019-01-01'), $internalUser),
        );

        $this->assertSame(
            'SELECT ipw' . self::FROM . self::STOCK_JOINS . self::OPEN_WHERE . $expectedExtra,
            $qb->getDQL(),
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('internalUserProvider')]
    public function testFetchOpenWindowsByTypeYear(bool $internalUser, string $expectedExtra): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchOpenWindowsByTypeYear(1, new DateTime('2019-01-01'), 2019, $internalUser),
        );

        $this->assertSame(
            'SELECT ipw, ipr, ips' . self::FROM . self::STOCK_JOINS
            . ' INNER JOIN ips.irhpPermitRanges ipr'
            . self::OPEN_WHERE
            . ' AND (ips.validTo BETWEEN :fromDate AND :toDate)'
            . $expectedExtra,
            $qb->getDQL(),
        );
        $this->assertSame(
            '2019-01-01 00:00:00',
            $qb->getParameter('fromDate')->getValue()->format('Y-m-d H:i:s'),
        );
        $this->assertSame(
            '2019-12-31 23:59:59',
            $qb->getParameter('toDate')->getValue()->format('Y-m-d H:i:s'),
        );
    }

    public static function internalUserProvider(): \Iterator
    {
        yield 'self serve hides hidden stocks' => [false, ' AND ips.hiddenSs <> 1'];
        yield 'internal sees everything' => [true, ''];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('overlappingProvider')]
    public function testFindOverlappingWindowsByType(?int $excluded, string $expectedExtra): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->findOverlappingWindowsByType(1, '2019-01-01', '2019-12-31', $excluded),
        );

        $this->assertSame(
            'SELECT ipw' . self::FROM
            . ' WHERE ((ipw.startDate BETWEEN :proposedStartDate AND :proposedEndDate)'
            . ' OR (ipw.endDate BETWEEN :proposedStartDate AND :proposedEndDate)'
            . ' OR (:proposedStartDate BETWEEN ipw.startDate AND ipw.endDate))'
            . ' AND ipw.irhpPermitStock = :irhpPermitStock'
            . $expectedExtra,
            $qb->getDQL(),
        );
    }

    public static function overlappingProvider(): \Iterator
    {
        yield 'without an excluded window' => [null, ''];
        yield 'excluding a window' => [7, ' AND ipw.id <> :irhpPermitWindow'];
    }
}
