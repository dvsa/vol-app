<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\AvailableTypes;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitType as IrhpPermitTypeRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Service\Permits\Availability\WindowAvailabilityChecker;
use Dvsa\Olcs\Transfer\Query\Permits\AvailableTypes as AvailableTypesQuery;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use DateTime;

class AvailableTypesTest extends QueryHandlerTestCase
{
    private $irhpPermitType1;

    private $irhpPermitType2;

    private $irhpPermitType3;

    private $irhpPermitTypes;

    public function setUp(): void
    {
        $this->sut = new AvailableTypes();
        $this->mockRepo('IrhpPermitType', IrhpPermitTypeRepo::class);

        $this->mockedSmServices = [
            'PermitsAvailabilityWindowAvailabilityChecker' => m::mock(WindowAvailabilityChecker::class)
        ];

        $this->irhpPermitType1 = m::mock(IrhpPermitType::class);
        $this->irhpPermitType1->shouldReceive('isEcmtShortTerm')
            ->andReturn(false);

        $this->irhpPermitType2 = m::mock(IrhpPermitType::class);
        $this->irhpPermitType2->shouldReceive('isEcmtShortTerm')
            ->andReturn(true);

        $this->irhpPermitType3 = m::mock(IrhpPermitType::class);
        $this->irhpPermitType3->shouldReceive('isEcmtShortTerm')
            ->andReturn(false);

        $this->irhpPermitTypes = [
            $this->irhpPermitType1,
            $this->irhpPermitType2,
            $this->irhpPermitType3
        ];

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = AvailableTypesQuery::create([]);

        $this->mockedSmServices['PermitsAvailabilityWindowAvailabilityChecker']->shouldReceive('hasAvailability')
            ->once()
            ->with(m::type(DateTime::class))
            ->andReturn(true);

        $this->repoMap['IrhpPermitType']->shouldReceive('fetchAvailableTypes')
            ->once()
            ->with(m::type(DateTime::class))
            ->andReturn($this->irhpPermitTypes);

        $expectedIrhpPermitTypes = [
            $this->irhpPermitType1,
            $this->irhpPermitType2,
            $this->irhpPermitType3
        ];

        $this->assertEquals(
            [
                'types' => $expectedIrhpPermitTypes,
                'hasTypes' => true,
            ],
            $this->sut->handleQuery($query)
        );
    }

    public function testHandleQueryShortTermUnavailable(): void
    {
        $query = AvailableTypesQuery::create([]);

        $this->mockedSmServices['PermitsAvailabilityWindowAvailabilityChecker']->shouldReceive('hasAvailability')
            ->once()
            ->with(m::type(DateTime::class))
            ->andReturn(false);

        $this->repoMap['IrhpPermitType']->shouldReceive('fetchAvailableTypes')
            ->once()
            ->with(m::type(DateTime::class))
            ->andReturn($this->irhpPermitTypes);

        $expectedIrhpPermitTypes = [
            $this->irhpPermitType1,
            $this->irhpPermitType3
        ];

        $this->assertEquals(
            [
                'types' => $expectedIrhpPermitTypes,
                'hasTypes' => true,
            ],
            $this->sut->handleQuery($query)
        );
    }

    public function testHandleQueryNoTypesAvailable(): void
    {
        $query = AvailableTypesQuery::create([]);

        $this->repoMap['IrhpPermitType']->shouldReceive('fetchAvailableTypes')
            ->once()
            ->with(m::type(DateTime::class))
            ->andReturn([]);

        $this->assertEquals(
            [
                'types' => [],
                'hasTypes' => false,
            ],
            $this->sut->handleQuery($query)
        );
    }
}
