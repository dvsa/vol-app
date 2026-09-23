<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Dvsa\Olcs\Api\Domain\Repository\Queue as Repo;
use Dvsa\Olcs\Api\Entity\Queue\Queue as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

final class QueueTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' q';

    /** The next item is the oldest queued one whose postponement, if any, has elapsed. */
    private const string NEXT_ITEM_WHERE = ' WHERE q.status = :statusId'
        . ' AND (q.processAfterDate <= :processAfter OR q.processAfterDate IS NULL)';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('typeFilterProvider')]
    public function testGetNextItem(array $includeTypes, array $excludeTypes, string $expectedExtra): void
    {
        $item = m::mock(Entity::class);
        $item->expects('incrementAttempts');
        $item->expects('setStatus')->with(m::type(RefData::class));

        $qb = $this->createRealQb()->willReturn([$item]);

        $this->em->shouldReceive('getReference')->andReturn(m::mock(RefData::class));
        $this->sut->expects('save')->with($item);

        $this->assertSame($item, $this->sut->getNextItem($includeTypes, $excludeTypes));

        $this->assertSame(
            'SELECT q' . self::FROM . self::NEXT_ITEM_WHERE . $expectedExtra . ' ORDER BY q.id ASC',
            $qb->getDQL(),
        );
        $this->assertSame(Entity::STATUS_QUEUED, $qb->getParameter('statusId')->getValue());
        $this->assertSame(1, $qb->getMaxResults());
    }

    public static function typeFilterProvider(): \Iterator
    {
        yield 'no type filter' => [[], [], ''];
        yield 'include types' => [['t1'], [], ' AND q.type IN(:includeTypes)'];
        yield 'exclude types' => [[], ['t2'], ' AND q.type NOT IN(:excludeTypes)'];
        yield 'both' => [
            ['t1'],
            ['t2'],
            ' AND q.type IN(:includeTypes) AND q.type NOT IN(:excludeTypes)',
        ];
    }

    public function testGetNextItemReturnsNullWhenTheQueueIsEmpty(): void
    {
        $this->createRealQb()->willReturn([]);

        $this->assertNull($this->sut->getNextItem());
    }

    /**
     * The postponed variant drops the processAfterDate condition entirely and orders by it, so
     * a postponed item can be picked up before its time.
     */
    public function testFetchNextItemIncludingPostponed(): void
    {
        $item = m::mock(Entity::class);

        $qb = $this->createRealQb()->willReturn([$item]);

        $this->assertSame($item, $this->sut->fetchNextItemIncludingPostponed(['t1'], ['t2']));

        $this->assertSame(
            'SELECT q' . self::FROM
            . ' WHERE q.status = :statusId AND q.type IN(:includeTypes)'
            . ' AND q.type NOT IN(:excludeTypes)'
            . ' ORDER BY q.processAfterDate ASC',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getMaxResults());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('emptinessProvider')]
    public function testIsItemTypeQueued(array $results, bool $expected): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getArrayResult')->andReturn($results);

        $this->assertSame($expected, $this->sut->isItemTypeQueued('t1'));

        $this->assertSame(
            'SELECT q' . self::FROM . self::NEXT_ITEM_WHERE . ' AND q.type = :type'
            . ' ORDER BY q.id ASC',
            $qb->getDQL(),
        );
        $this->assertSame('t1', $qb->getParameter('type')->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('emptinessProvider')]
    public function testIsItemInQueue(array $results, bool $expected): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getArrayResult')->andReturn($results);

        $this->assertSame($expected, $this->sut->isItemInQueue(['t1'], ['s1']));

        $this->assertSame(
            'SELECT q.id' . self::FROM
            . ' WHERE q.type IN(:types) AND q.status IN(:statuses)',
            $qb->getDQL(),
        );
        $this->assertSame(['t1'], $qb->getParameter('types')->getValue());
        $this->assertSame(['s1'], $qb->getParameter('statuses')->getValue());
        $this->assertSame(1, $qb->getMaxResults());
    }

    public static function emptinessProvider(): \Iterator
    {
        yield 'found' => [[['id' => 1]], true];
        yield 'not found' => [[], false];
    }

    /**
     * Continuation-not-sought rows are inserted with one placeholder triple per licence, built
     * into a single raw INSERT.
     */
    public function testEnqueueContinuationNotSought(): void
    {
        $licences = [
            ['id' => 1, 'version' => 2],
            ['id' => 3, 'version' => 4],
        ];

        $result = m::mock(Result::class);
        $result->expects('rowCount')->andReturn(2);

        $statement = m::mock(Statement::class);
        $statement->expects('bindValue')->with('status1', Entity::STATUS_QUEUED);
        $statement->expects('bindValue')->with('type1', Entity::TYPE_CNS);
        $statement->expects('bindValue')->with('options1', '{"id":1,"version":2}');
        $statement->expects('bindValue')->with('status2', Entity::STATUS_QUEUED);
        $statement->expects('bindValue')->with('type2', Entity::TYPE_CNS);
        $statement->expects('bindValue')->with('options2', '{"id":3,"version":4}');
        $statement->expects('executeQuery')->andReturn($result);

        $connection = m::mock(Connection::class);
        $connection->expects('prepare')
            ->with(
                'INSERT INTO `queue` (`status`, `type`, `options`) VALUES '
                . '(:status1, :type1, :options1), (:status2, :type2, :options2)',
            )
            ->andReturn($statement);

        $this->em->expects('getConnection')->withNoArgs()->andReturn($connection);

        $this->assertSame(2, $this->sut->enqueueContinuationNotSought($licences));
    }
}
