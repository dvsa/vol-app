<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\ById as IrhpApplicationByIdHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Permits\Fees\DaysToPayIssueFeeProvider;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ById as QryClass;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Exception;
use Mockery as m;

/**
 * ById Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class ByIdTest extends QueryHandlerTestCase
{
    protected $bundle = [
        'licence' => ['trafficArea', 'organisation'],
        'irhpPermitType' => ['name'],
        'fees' => ['feeType' => ['feeType'], 'feeStatus'],
        'irhpPermitApplications' => ['irhpPermitWindow' => ['irhpPermitStock' => ['country', 'irhpPermitType']]],
        'sectors',
        'countrys'
    ];

    public function setUp(): void
    {
        $this->sut = m::mock(IrhpApplicationByIdHandler::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices['PermitsFeesDaysToPayIssueFeeProvider'] = m::mock(DaysToPayIssueFeeProvider::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $canViewCandidatePermits = true;
        $canSelectCandidatePermits = false;
        $totalPermitsAwarded = 5;
        $totalPermitsRequired = 10;
        $daysToPayIssueFee = 10;

        $this->mockedSmServices['PermitsFeesDaysToPayIssueFeeProvider']->shouldReceive('getDays')
            ->withNoArgs()
            ->andReturn($daysToPayIssueFee);

        $fee1 = m::mock(Fee::class);
        $fee1->shouldReceive('setDaysToPayIssueFee')
            ->with($daysToPayIssueFee)
            ->once();
        $fee2 = m::mock(Fee::class);
        $fee2->shouldReceive('setDaysToPayIssueFee')
            ->with($daysToPayIssueFee)
            ->once();
        $fees = [$fee1, $fee2];

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('serialize')
            ->once()
            ->with($this->bundle)
            ->andReturn(['results' => ['serialized']])
            ->shouldReceive('canViewCandidatePermits')
            ->once()
            ->withNoArgs()
            ->andReturn($canViewCandidatePermits)
            ->shouldReceive('canSelectCandidatePermits')
            ->once()
            ->withNoArgs()
            ->andReturn($canSelectCandidatePermits)
            ->shouldReceive('calculateTotalPermitsRequired')
            ->once()
            ->withNoArgs()
            ->andReturn($totalPermitsRequired)
            ->shouldReceive('getPermitsAwarded')
            ->once()
            ->withNoArgs()
            ->andReturn($totalPermitsAwarded)
            ->shouldReceive('getFees')
            ->once()
            ->withNoArgs()
            ->andReturn($fees);

        $query = QryClass::create(['id' => 1]);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($irhpApplication);

        $this->sut->shouldReceive('auditRead')
            ->once()
            ->with($irhpApplication);

        $this->assertEquals(
            [
                'results' => ['serialized'],
                'canViewCandidatePermits' => $canViewCandidatePermits,
                'canSelectCandidatePermits' => $canSelectCandidatePermits,
                'totalPermitsAwarded' => $totalPermitsAwarded,
                'totalPermitsRequired' => $totalPermitsRequired,
            ],
            $this->sut->handleQuery($query)->serialize()
        );
    }

    public function testHandleQueryWithException(): void
    {
        $canViewCandidatePermits = true;
        $canSelectCandidatePermits = false;
        $daysToPayIssueFee = 10;

        $this->mockedSmServices['PermitsFeesDaysToPayIssueFeeProvider']->shouldReceive('getDays')
            ->withNoArgs()
            ->andReturn($daysToPayIssueFee);

        $fee1 = m::mock(Fee::class);
        $fee1->shouldReceive('setDaysToPayIssueFee')
            ->with($daysToPayIssueFee)
            ->once();
        $fee2 = m::mock(Fee::class);
        $fee2->shouldReceive('setDaysToPayIssueFee')
            ->with($daysToPayIssueFee)
            ->once();
        $fees = [$fee1, $fee2];

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('serialize')
            ->once()
            ->with($this->bundle)
            ->andReturn(['results' => ['serialized']])
            ->shouldReceive('canViewCandidatePermits')
            ->once()
            ->withNoArgs()
            ->andReturn($canViewCandidatePermits)
            ->shouldReceive('canSelectCandidatePermits')
            ->once()
            ->withNoArgs()
            ->andReturn($canSelectCandidatePermits)
            ->shouldReceive('calculateTotalPermitsRequired')
            ->once()
            ->withNoArgs()
            ->andReturn(10)
            ->shouldReceive('getPermitsAwarded')
            ->once()
            ->withNoArgs()
            ->andThrow(new Exception('error'))
            ->shouldReceive('getFees')
            ->once()
            ->withNoArgs()
            ->andReturn($fees);

        $query = QryClass::create(['id' => 1]);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($irhpApplication);

        $this->sut->shouldReceive('auditRead')
            ->once()
            ->with($irhpApplication);

        $this->assertEquals(
            [
                'results' => ['serialized'],
                'canViewCandidatePermits' => $canViewCandidatePermits,
                'canSelectCandidatePermits' => $canSelectCandidatePermits,
                'totalPermitsAwarded' => null,
                'totalPermitsRequired' => null,
            ],
            $this->sut->handleQuery($query)->serialize()
        );
    }
}
