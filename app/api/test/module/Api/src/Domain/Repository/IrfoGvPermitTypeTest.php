<?php

declare(strict_types=1);

/**
 * IrfoGvPermitType test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\IrfoGvPermitType as Repo;
use Mockery as m;

/**
 * IrfoGvPermitType test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class IrfoGvPermitTypeTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testApplyListFilters(): void
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $mockQ = m::mock(\Dvsa\Olcs\Transfer\Query\QueryInterface::class);
        $mockQb->shouldReceive('orderBy')->with('m.description', 'ASC')->once()->andReturnSelf();

        $this->sut->applyListFilters($mockQb, $mockQ);
    }

    public function testFetchActiveRecords(): void
    {
        $qb = $this->createMockQb('QRYSTART');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')
            ->once()
            ->andReturn(m::mock(\Doctrine\ORM\AbstractQuery::class)->shouldReceive('getResult')
                ->once()
                ->andReturn(['Mocked Result'])
                ->getMock());

        $this->assertEquals(['Mocked Result'], $this->sut->fetchActiveRecords('ORG1'));

        $actualQuery = $this->query;
        $expectedPattern = '/QRYSTART AND \(m\.displayUntil IS NULL OR m\.displayUntil >= \[\[.*\]\]\) ORDER BY m\.description ASC/';
        $this->assertMatchesRegularExpression($expectedPattern, $actualQuery);
    }
}
