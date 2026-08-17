<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Mockery as m;

/**
 * VehicleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class VehicleTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(\Dvsa\Olcs\Api\Domain\Repository\Vehicle::class);
    }

    public function testFetchByVrm(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $query = m::mock(Query::class);

        $query->shouldReceive('execute')
            ->andReturnSelf();

        $query->shouldReceive('getResult')
            ->andReturn(['RESULTS']);

        $qb->shouldReceive('getQuery')
            ->andReturn($query);
        $this->assertEquals(['RESULTS'], $this->sut->fetchByVrm('ABC123'));

        $expectedQuery = 'BLAH AND m.vrm = [[ABC123]]';
        $this->assertEquals($expectedQuery, $this->query);
    }
}
