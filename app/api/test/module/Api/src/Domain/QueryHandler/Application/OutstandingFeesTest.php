<?php

declare(strict_types=1);

/**
 * Outstanding Fees Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\OutstandingFees;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Transfer\Query\Application\OutstandingFees as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Service\FeesHelperService;
use Mockery as m;

/**
 * Outstanding Fees Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OutstandingFeesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new OutstandingFees();
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('SystemParameter', \Dvsa\Olcs\Api\Domain\Repository\SystemParameter::class);
        $this->mockedSmServices['FeesHelperService'] = m::mock(FeesHelperService::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $organisationId = 69;

        $query = Qry::create(['id' => $organisationId, 'hideExpired' => false]);

        $mockApplication = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class)
            ->shouldReceive('getId')
            ->andReturn($organisationId)
            ->shouldReceive('serialize')
            ->getMock();

        $fees = [
            $this->getMockFee(98),
            $this->getMockFee(99),
        ];

        $this->mockedSmServices['FeesHelperService']
            ->shouldReceive('getOutstandingFeesForApplication')
            ->with(69)
            ->andReturn($fees)
            ->once();

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockApplication);

        $this->repoMap['SystemParameter']
            ->shouldReceive('getDisableSelfServeCardPayments')
            ->once()
            ->with()
            ->andReturn(true);

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);
    }

    private function getMockFee(mixed $feeId): m\MockInterface
    {
        return m::mock()
            ->shouldReceive('serialize')
            ->shouldReceive('getId')
            ->andReturn($feeId)
            ->getMock();
    }
}
