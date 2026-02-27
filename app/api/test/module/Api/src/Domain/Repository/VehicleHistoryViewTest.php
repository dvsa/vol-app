<?php

declare(strict_types=1);

/**
 * VehicleHistoryView test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\VehicleHistoryView as VehicleHistoryViewRepo;

/**
 * VehicleHistoryView test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehicleHistoryViewTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(VehicleHistoryViewRepo::class);
    }

    public function testFetchByVrm(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getArrayResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchByVrm('ABC123'));

        $expectedQuery = 'BLAH AND m.vrm = [[ABC123]] AND m.id IS NOT NULL ORDER BY m.specifiedDate DESC';
        $this->assertEquals($expectedQuery, $this->query);
    }
}
