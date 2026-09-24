<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepo;
use Dvsa\Olcs\Api\Entity\Publication\Publication as Entity;
use Dvsa\Olcs\Transfer\Query\Publication\PendingList;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class PublicationTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(PublicationRepo::class, true);
    }

    public function testFetchLatestForTrafficAreaAndType(): void
    {
        $publication = m::mock(Entity::class);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([$publication]);

        $this->assertSame($publication, $this->sut->fetchLatestForTrafficAreaAndType('M', 'A&D'));

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.trafficArea = :trafficArea AND m.pubType = :pubType'
            . ' AND m.pubStatus = :pubStatus',
            $qb->getDQL(),
        );
        $this->assertSame('M', $qb->getParameter('trafficArea')->getValue());
        $this->assertSame('A&D', $qb->getParameter('pubType')->getValue());
        $this->assertSame(Entity::PUB_NEW_STATUS, $qb->getParameter('pubStatus')->getValue());
    }

    public function testFetchLatestForTrafficAreaAndTypeNotFound(): void
    {
        $this->createRealQb()->stubbedQuery()->expects('getResult')->andReturn([]);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchLatestForTrafficAreaAndType('M', 'A&D');
    }

    public function testFetchPendingList(): void
    {
        $results = [m::mock(Entity::class)];

        $qb = $this->createRealQb()->willReturn($results);

        $query = m::mock(PendingList::class);
        $this->sut->expects('buildDefaultListQuery')->with($qb, $query)->andReturnSelf();
        $this->sut->expects('fetchPaginatedCount')->with($qb)->andReturn(1);

        $this->assertSame(
            ['results' => $results, 'count' => 1],
            $this->sut->fetchPendingList($query),
        );

        $this->assertSame(
            'SELECT m' . self::FROM . ' WHERE m.pubStatus IN(:pubStatus)',
            $qb->getDQL(),
        );
        $this->assertSame(
            [Entity::PUB_NEW_STATUS, Entity::PUB_GENERATED_STATUS],
            $qb->getParameter('pubStatus')->getValue(),
        );
    }

    /**
     * Publication type and traffic area are both optional narrowings on top of the printed
     * status and the date window.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('publishedListProvider')]
    public function testFetchPublishedList(?string $pubType, ?string $trafficAreaId, string $expectedExtra): void
    {
        $results = [m::mock(Entity::class)];

        $qb = $this->createRealQb()->willReturn($results);

        $query = m::mock(QueryInterface::class);
        $this->sut->expects('buildDefaultListQuery')->with($qb, $query)->andReturnSelf();
        $this->sut->expects('fetchPaginatedCount')->with($qb)->andReturn(1);

        $this->assertSame(
            ['results' => $results, 'count' => 1],
            $this->sut->fetchPublishedList($query, $pubType, '2015-01-01', '2015-02-01', $trafficAreaId),
        );

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.pubStatus = :pubStatus AND m.pubDate >= :pubDateFrom AND m.pubDate < :pubDateTo'
            . $expectedExtra,
            $qb->getDQL(),
        );
        $this->assertSame(Entity::PUB_PRINTED_STATUS, $qb->getParameter('pubStatus')->getValue());
        $this->assertSame('2015-01-01', $qb->getParameter('pubDateFrom')->getValue());
        $this->assertSame('2015-02-01', $qb->getParameter('pubDateTo')->getValue());
    }

    public static function publishedListProvider(): \Iterator
    {
        yield 'neither' => [null, null, ''];
        yield 'pub type only' => ['A&D', null, ' AND m.pubType = :pubType'];
        yield 'traffic area only' => [null, 'M', ' AND m.trafficArea = :trafficArea'];
        yield 'both' => ['A&D', 'M', ' AND m.pubType = :pubType AND m.trafficArea = :trafficArea'];
    }
}
