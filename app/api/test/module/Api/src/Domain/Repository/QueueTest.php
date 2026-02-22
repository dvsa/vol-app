<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\Queue as QueueRepo;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

class QueueTest extends RepositoryTestCase
{
    /**
     * @var \Dvsa\Olcs\Api\Domain\Repository\Queue
     */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(QueueRepo::class, true);
    }

    public function testGetNextItem(): void
    {
        $item = m::mock(QueueEntity::class)->makePartial();

        $qb = $this->createMockQb('[QUERY]');
        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('order')->with('id', 'ASC')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn([$item])
                ->getMock()
        );

        $ref = m::mock(RefData::class)->makePartial();
        $this->sut->shouldReceive('getRefdataReference')
            ->with(QueueEntity::STATUS_PROCESSING)
            ->once()
            ->andReturn($ref);
        $this->sut
            ->shouldReceive('save')
            ->with($item)
            ->once();

        $this->assertEquals($item, $this->sut->getNextItem());

        $now = new DateTime();
        $expectedQuery = '[QUERY] AND q.status = [[que_sts_queued]] AND ' .
            '(q.processAfterDate <= [[' . $now->format(DateTime::W3C) . ']] OR q.processAfterDate IS NULL) LIMIT 1';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testGetNextItemInclude(): void
    {
        $item = m::mock(QueueEntity::class)->makePartial();

        $qb = $this->createMockQb('[QUERY]');
        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('order')->with('id', 'ASC')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn([$item])
                ->getMock()
        );

        $ref = m::mock(RefData::class)->makePartial();
        $this->sut->shouldReceive('getRefdataReference')
            ->with(QueueEntity::STATUS_PROCESSING)
            ->once()
            ->andReturn($ref);
        $this->sut
            ->shouldReceive('save')
            ->with($item)
            ->once();

        $this->assertEquals($item, $this->sut->getNextItem(['foo']));

        $now = new DateTime();
        $expectedQuery = '[QUERY] AND q.status = [[que_sts_queued]] AND' .
            ' (q.processAfterDate <= [[' . $now->format(DateTime::W3C) . ']] OR q.processAfterDate IS NULL) LIMIT 1' .
            ' AND q.type IN [[["foo"]]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testGetNextItemExclude(): void
    {
        $item = m::mock(QueueEntity::class)->makePartial();

        $qb = $this->createMockQb('[QUERY]');
        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('order')->with('id', 'ASC')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn([$item])
                ->getMock()
        );

        $ref = m::mock(RefData::class)->makePartial();
        $this->sut->shouldReceive('getRefdataReference')
            ->with(QueueEntity::STATUS_PROCESSING)
            ->once()
            ->andReturn($ref);
        $this->sut
            ->shouldReceive('save')
            ->with($item)
            ->once();

        $this->assertEquals($item, $this->sut->getNextItem(['foo'], ['bar']));

        $now = new DateTime();
        $expectedQuery = '[QUERY] AND q.status = [[que_sts_queued]] AND' .
            ' (q.processAfterDate <= [[' . $now->format(DateTime::W3C) . ']] OR q.processAfterDate IS NULL) LIMIT 1' .
            ' AND q.type IN [[["foo"]]] AND q.type NOT IN [[["bar"]]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testEnqueueContinuationNotSought(): void
    {
        $options1 = '{"id":1,"version":2}';
        $options2 = '{"id":3,"version":4}';

        $query = 'INSERT INTO `queue` (`status`, `type`, `options`) VALUES '
            . '(:status1, :type1, :options1), (:status2, :type2, :options2)';

        $queryResult = m::mock(Result::class);
        $queryResult->expects('rowCount')
            ->withNoArgs()
            ->andReturn(2);

        $mockStatement = m::mock(Statement::class);
        $mockStatement ->expects('executeQuery')
            ->withNoArgs()
            ->andReturn($queryResult);
        $mockStatement->expects('bindValue')->with('status1', QueueEntity::STATUS_QUEUED);
        $mockStatement->expects('bindValue')->with('type1', QueueEntity::TYPE_CNS);
        $mockStatement->expects('bindValue')->with('options1', $options1);
        $mockStatement->expects('bindValue')->with('status2', QueueEntity::STATUS_QUEUED);
        $mockStatement->expects('bindValue')->with('type2', QueueEntity::TYPE_CNS);
        $mockStatement->expects('bindValue')->with('options2', $options2);

        $mockConnection = m::mock(Connection::class);
        $mockConnection->expects('prepare')
            ->with($query)
            ->andReturn($mockStatement);

        $this->em->expects('getConnection')->withNoArgs()->andReturn($mockConnection);

        $licences = [
            ['id' => 1, 'version' => 2],
            ['id' => 3, 'version' => 4]
        ];

        $this->assertEquals(2, $this->sut->enqueueContinuationNotSought($licences));
    }

    public function testIsItemTypeQueuedTrue(): void
    {
        $qb = $this->createMockQb('[QUERY]');
        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('order')->with('id', 'ASC')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getArrayResult')->with()->once()->andReturn(['X']);

        $this->assertTrue($this->sut->isItemTypeQueued('foo'));

        $now = new DateTime();
        $expectedQuery = '[QUERY] AND q.status = [[que_sts_queued]] AND' .
            ' (q.processAfterDate <= [[' . $now->format(DateTime::W3C) . ']] OR q.processAfterDate IS NULL) LIMIT 1' .
            ' AND q.type = [[foo]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testIsItemTypeQueuedFalse(): void
    {
        $qb = $this->createMockQb('[QUERY]');
        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('order')->with('id', 'ASC')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getArrayResult')->with()->once()->andReturn([]);

        $this->assertFalse($this->sut->isItemTypeQueued('foo'));

        $now = new DateTime();
        $expectedQuery = '[QUERY] AND q.status = [[que_sts_queued]] AND' .
            ' (q.processAfterDate <= [[' . $now->format(DateTime::W3C) . ']] OR q.processAfterDate IS NULL) LIMIT 1' .
            ' AND q.type = [[foo]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchNextItemIncludingPostponedWithIncludeTypes(): void
    {
        $item = m::mock(QueueEntity::class)->makePartial();

        $qb = $this->createMockQb('[QUERY]');
        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('order')->with('q.processAfterDate', 'ASC')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn([$item])
                ->getMock()
        );

        $this->assertEquals($item, $this->sut->fetchNextItemIncludingPostponed(['foo']));

        $expectedQuery = '[QUERY] AND q.status = [[que_sts_queued]] LIMIT 1' .
            ' AND q.type IN [[["foo"]]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchNextItemIncludingPostponedWithexcludeTypes(): void
    {
        $item = m::mock(QueueEntity::class)->makePartial();

        $qb = $this->createMockQb('[QUERY]');
        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('order')->with('q.processAfterDate', 'ASC')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn([$item])
                ->getMock()
        );

        $this->assertEquals($item, $this->sut->fetchNextItemIncludingPostponed(['foo'], ['bar']));
        $expectedQuery = '[QUERY] AND q.status = [[que_sts_queued]] LIMIT 1' .
            ' AND q.type IN [[["foo"]]] AND q.type NOT IN [[["bar"]]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    /**
     * @param array $results
     * @param bool  $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsItemInQueue')]
    public function testIsItemInQueue(mixed $results, mixed $expected): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getArrayResult')
                ->andReturn($results)
                ->getMock()
        );
        $this->assertEquals($expected, $this->sut->isItemInQueue(['T1', 'T2'], ['S1', 'S2']));

        $expectedQuery = 'BLAH '
            . 'SELECT q.id '
            . 'AND q.type IN [[["T1","T2"]]] '
            . 'AND q.status IN [[["S1","S2"]]] '
            . 'LIMIT 1';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public static function dpIsItemInQueue(): array
    {
        return [
            'exists in the queue' => [['RESULTS'], true],
            'not in the queue' => [[], false],
        ];
    }
}
