<?php

declare(strict_types=1);

/**
 * Outstanding Fees Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Organisation;

use Dvsa\Olcs\Api\Domain\QueryHandler\Organisation\OutstandingFees;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Transfer\Query\Organisation\OutstandingFees as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Outstanding Fees Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OutstandingFeesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new OutstandingFees();
        $this->mockRepo('Organisation', OrganisationRepo::class);
        $this->mockRepo('Fee', FeeRepo::class);
        $this->mockRepo('SystemParameter', \Dvsa\Olcs\Api\Domain\Repository\SystemParameter::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $organisationId = 69;

        $query = Qry::create(['id' => $organisationId, 'hideExpired' => false]);

        $mockOrganisation = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class)
            ->shouldReceive('getId')
            ->andReturn($organisationId)
            ->shouldReceive('serialize')
            ->getMock();

        $fees = [
            $this->getMockFee(98),
            $this->getMockFee(99),
        ];

        $this->repoMap['Organisation']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockOrganisation);

        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByOrganisationId')
            ->once()
            ->with($organisationId, false, true)
            ->andReturn($fees);

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
