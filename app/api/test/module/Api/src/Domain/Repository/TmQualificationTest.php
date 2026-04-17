<?php

declare(strict_types=1);

/**
 * TmQualification test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\TmQualification as TmQualificationRepo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * TmQualification test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmQualificationTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(TmQualificationRepo::class);
    }

    public function testApplyListFilters(): void
    {
        $sut = m::mock(TmQualificationRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $mockQuery = m::mock(QueryInterface::class);
        $mockQuery->shouldReceive('getTransportManager')
            ->andReturn(1)
            ->once()
            ->getMock();

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('tq.transportManager', ':transportManager')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('transportManager', 1)->once()->andReturnSelf();

        $sut->applyListFilters($mockQb, $mockQuery);
    }

    public function testApplyListJoins(): void
    {
        $sut = m::mock(TmQualificationRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $sut->shouldReceive('getQueryBuilder')->with()->once()->andReturn($mockQb);
        $mockQb->shouldReceive('orderBy')->with('qt.displayOrder', 'ASC')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('countryCode', 'cc')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('qualificationType', 'qt')->once()->andReturnSelf();

        $sut->applyListJoins($mockQb);
    }
}
