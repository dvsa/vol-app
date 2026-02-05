<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\RangesByIrhpApplication as RangesByIrhpApplicationHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Service\Permits\Availability\CandidatePermitsAvailableCountCalculator;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\RangesByIrhpApplication as QryClass;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * RangesByIrhpApplication Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class RangesByIrhpApplicationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new RangesByIrhpApplicationHandler();
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices = [
            'PermitsAvailabilityCandidatePermitsAvailableCountCalculator' => m::mock(CandidatePermitsAvailableCountCalculator::class),
        ];

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = QryClass::create(['irhpApplication' => 100011]);

        $irhpApplication = m::mock(IrhpApplicationEntity::class);

        $range1 = m::mock(IrhpPermitRange::class)->makePartial();
        $range2 = m::mock(IrhpPermitRange::class)->makePartial();

        $range1->setId(1111);
        $range2->setId(2222);

        $ranges = new ArrayCollection([$range1, $range2]);

        $this->mockedSmServices['PermitsAvailabilityCandidatePermitsAvailableCountCalculator']
            ->shouldReceive('getCount')
            ->once()
            ->with($range1, 0)
            ->andReturn(40);

        $this->mockedSmServices['PermitsAvailabilityCandidatePermitsAvailableCountCalculator']
            ->shouldReceive('getCount')
            ->once()
            ->with($range2, 0)
            ->andReturn(22);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($query->getIrhpApplication())
            ->andReturn($irhpApplication);

        $irhpApplication
            ->shouldReceive('getAssociatedStock->getNonReservedNonReplacementRangesOrderedByFromNo')
            ->once()
            ->withNoArgs()
            ->andReturn($ranges);

        $result = $this->sut->handleQuery($query);

        $this->assertIsArray(
            $result
        );

        $this->assertArrayHasKey(
            'ranges',
            $result
        );

        $this->assertEquals(
            2,
            $result['count']
        );

        $this->assertEquals(
            40,
            $result['ranges'][0]['remainingPermits']
        );

        $this->assertEquals(
            1111,
            $result['ranges'][0]['id']
        );

        $this->assertEquals(
            2222,
            $result['ranges'][1]['id']
        );

        $this->assertEquals(
            22,
            $result['ranges'][1]['remainingPermits']
        );
    }

    public function testHandleQueryNoRanges(): void
    {
        $query = QryClass::create(['irhpApplication' => 100011]);

        $irhpApplication = m::mock(IrhpApplicationEntity::class);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($query->getIrhpApplication())
            ->andReturn($irhpApplication);

        $irhpApplication
            ->shouldReceive('getAssociatedStock->getNonReservedNonReplacementRangesOrderedByFromNo')
            ->once()
            ->withNoArgs()
            ->andReturn([]);

        $this->assertEquals(
            [
                'count' => 0,
                'ranges' => []
            ],
            $this->sut->handleQuery($query)
        );
    }
}
