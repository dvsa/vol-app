<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\AbstractReadAudit;
use Mockery as m;

/**
 * Class with common functionality for testing Read Audit functionality
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
abstract class AbstractReadAuditTestCase extends RepositoryTestCase
{
    /** @var AbstractReadAudit|m\MockInterface */
    protected $sut;

    protected function commonTestFetchOneOrMore(mixed $entityProperty): void
    {
        $userId = 111;
        $entityId = 222;
        $date = new \DateTime('2013-12-11 10:09:08');

        $qb = $this->createMockQb('{{QUERY}}');
        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery->getResult')->andReturn(['foo']);

        static::assertEquals(['foo'], $this->sut->fetchOneOrMore($userId, $entityId, $date));

        $expected = '{{QUERY}} AND m.user = [[111]]' .
            ' AND m.' . $entityProperty . ' = [[222]]' .
            ' AND m.createdOn >= [[2013-12-11T00:00:00+00:00]]' .
            ' AND m.createdOn <= [[2013-12-11T23:59:59+00:00]]';

        static::assertEquals($expected, $this->query);
    }

    protected function commonTestDeleteOlderThan(mixed $entityClass): void
    {
        $query = m::mock()
            ->shouldReceive('setParameter')
            ->once()
            ->with('oldestDate', '2015-01-01')
            //
            ->shouldReceive('execute')
            ->once()
            ->andReturn(10)
            ->getMock();

        $this->em->shouldReceive('createQuery')
            ->once()
            ->with('DELETE FROM ' . $entityClass . ' e WHERE e.createdOn <= :oldestDate')
            ->andReturn($query);

        $result = $this->sut->deleteOlderThan('2015-01-01');

        static::assertEquals(10, $result);
    }

    protected function commonTestFetchList(mixed $queryDto, mixed $whereClause): void
    {
        $this->sut->shouldReceive('fetchPaginatedList')
            ->andReturn(['result']);

        $this->mockCreateQueryBuilder(
            $this->createMockQb('{{QUERY}}')
        );

        $qbHelper = m::mock();
        $qbHelper->shouldReceive('withRefdata')->once();
        $qbHelper->shouldReceive('paginate')->once();

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->andReturn($qbHelper);

        static::assertEquals(['result'], $this->sut->fetchList($queryDto, Query::HYDRATE_OBJECT));

        $expected = '{{QUERY}}' .
            ' INNER JOIN m.user u' .
            ' INNER JOIN u.contactDetails cd' .
            ' INNER JOIN cd.person p' .
            $whereClause .
            ' ORDER BY m.createdOn DESC';

        static::assertEquals($expected, $this->query);
    }
}
