<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Mockery as m;

/**
 * Shared coverage for the read-audit repositories, which differ only in the entity they audit
 * and the property naming it.
 */
abstract class AbstractReadAuditTestCase extends RepositoryTestCase
{
    protected $sut;

    /**
     * The supplied date bounds a whole day: from midnight to one second before the next.
     */
    protected function commonTestFetchOneOrMore(string $entityProperty): void
    {
        $date = new \DateTime('2013-12-11 10:09:08');

        $qb = $this->createRealQb()->willReturn(['foo']);

        $this->assertSame(['foo'], $this->sut->fetchOneOrMore(111, 222, $date));

        $this->assertStringEndsWith(
            ' WHERE m.user = :user AND m.' . $entityProperty . ' = :entityId'
            . ' AND m.createdOn >= :dateFrom AND m.createdOn <= :dateTo',
            $qb->getDQL(),
        );
        $this->assertSame(111, $qb->getParameter('user')->getValue());
        $this->assertSame(222, $qb->getParameter('entityId')->getValue());
        $this->assertSame('2013-12-11 00:00:00', $qb->getParameter('dateFrom')->getValue()->format('Y-m-d H:i:s'));
        $this->assertSame('2013-12-11 23:59:59', $qb->getParameter('dateTo')->getValue()->format('Y-m-d H:i:s'));
    }

    protected function commonTestDeleteOlderThan(string $entityClass): void
    {
        $query = m::mock(Query::class);
        $query->expects('setParameter')->with('oldestDate', '2015-01-01');
        $query->expects('execute')->andReturn(10);

        $this->em->expects('createQuery')
            ->with('DELETE FROM ' . $entityClass . ' e WHERE e.createdOn <= :oldestDate')
            ->andReturn($query);

        $this->assertSame(10, $this->sut->deleteOlderThan('2015-01-01'));
    }

    protected function commonTestFetchList(mixed $queryDto, string $entityProperty): void
    {
        $qb = $this->createRealQb();

        $this->sut->expects('fetchPaginatedList')->andReturn(['result']);

        $this->assertSame(['result'], $this->sut->fetchList($queryDto, Query::HYDRATE_OBJECT));

        $this->assertStringEndsWith(
            ' INNER JOIN m.user u INNER JOIN u.contactDetails cd INNER JOIN cd.person p'
            . ' WHERE m.' . $entityProperty . ' = :byEntity'
            . ' ORDER BY m.createdOn DESC',
            $qb->getDQL(),
        );
        $this->assertSame(111, $qb->getParameter('byEntity')->getValue());
    }
}
