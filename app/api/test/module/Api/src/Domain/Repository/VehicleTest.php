<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;

/**
 * VehicleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class VehicleTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(\Dvsa\Olcs\Api\Domain\Repository\Vehicle::class);
    }

    public function testFetchByVrm(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchByVrm('ABC123'));

        $expectedQuery = 'BLAH AND m.vrm = [[ABC123]]';
        $this->assertEquals($expectedQuery, $this->query);
    }
}
