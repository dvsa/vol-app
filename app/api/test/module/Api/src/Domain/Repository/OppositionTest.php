<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Opposition as Repo;
use Dvsa\Olcs\Api\Entity\Opposition\Opposition as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class OppositionTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchUsingId(): void
    {
        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')->andReturn(99);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['result']);

        $this->assertSame('result', $this->sut->fetchUsingId($command, Query::HYDRATE_OBJECT));

        // m.grounds is joined twice (w3 from withRefdata, w4 from the explicit with) — see the
        // migration findings.
        $this->assertSame(
            'SELECT m, w0, w1, w2, w3, o, w4, c, p, a, ct, pc FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.oppositionType w0 LEFT JOIN m.status w1 LEFT JOIN m.isValid w2'
            . ' LEFT JOIN m.grounds w3 LEFT JOIN m.opposer o LEFT JOIN m.grounds w4'
            . ' LEFT JOIN o.contactDetails c LEFT JOIN c.person p LEFT JOIN c.address a'
            . ' LEFT JOIN c.contactType ct LEFT JOIN c.phoneContacts pc'
            . ' WHERE m.id = :byId',
            $qb->getDQL(),
        );
        $this->assertSame(99, $qb->getParameter('byId')->getValue());
    }

    /**
     * The licence and application filters key off the 'ca' alias, which only exists once
     * buildDefaultListQuery() has joined it — so run them in the order fetchList() does.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('listFilterProvider')]
    public function testApplyListFilters(
        ?int $case,
        ?int $licence,
        ?int $application,
        string $expectedWhere,
        string $parameter,
        int $expectedValue,
    ): void {
        $qb = $this->createRealQb();

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getCase')->with()->andReturn($case);
        $query->shouldReceive('getLicence')->with()->andReturn($licence);
        $query->shouldReceive('getApplication')->with()->andReturn($application);

        $this->sut->buildDefaultListQuery($qb, $query);
        $this->sut->applyListFilters($qb, $query);

        $this->assertStringEndsWith(' WHERE ' . $expectedWhere, $qb->getDQL());
        $this->assertSame($expectedValue, $qb->getParameter($parameter)->getValue());
    }

    public static function listFilterProvider(): \Iterator
    {
        yield 'by case' => [746, null, null, 'm.case = :byCase', 'byCase', 746];
        yield 'by licence' => [null, 43, null, 'ca.licence = :licence', 'licence', 43];
        yield 'by application' => [null, null, 543, 'ca.application = :application', 'application', 543];
    }

    public function testFetchByApplicationId(): void
    {
        $qb = $this->createRealQb()->willReturn('result');

        $this->assertSame('result', $this->sut->fetchByApplicationId(69));

        $this->assertSame(
            'SELECT m, c FROM ' . Entity::class . ' m LEFT JOIN m.case c'
            . ' WHERE c.application = :application'
            . ' ORDER BY m.createdOn DESC',
            $qb->getDQL(),
        );
        $this->assertSame(69, $qb->getParameter('application')->getValue());
    }
}
