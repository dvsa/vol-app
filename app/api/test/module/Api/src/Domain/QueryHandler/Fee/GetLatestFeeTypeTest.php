<?php

declare(strict_types=1);

/**
 * Get latest fee type test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Fee;

use Doctrine\ORM\Query as DoctrineQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\Fee\GetLatestFeeType as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepo;
use Dvsa\Olcs\Transfer\Query\Fee\GetLatestFeeType as Qry;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Get latest fee type test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GetLatestFeeTypeTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('FeeType', FeeTypeRepo::class);

        parent::setUp();
    }

    public function testGetLatestFeeType(): void
    {
        $query = Qry::create(
            [
                'feeType' => 'CONT',
                'operatorType' => 'lcat_gv',
                'licenceType' => 'ltyp_si',
                'date' => '2015-01-01',
                'trafficArea' => 'B'
            ]
        );

        $mockFeeType = m::mock(RefData::class)->makePartial();
        $mockOperatorType = m::mock(RefData::class)->makePartial();
        $mockLicenceType = m::mock(RefData::class)->makePartial();

        $this->repoMap['FeeType']
            ->shouldReceive('fetchLatest')
            ->with($mockFeeType, $mockOperatorType, $mockLicenceType, m::type(\DateTime::class), 'B')
            ->once()
            ->andReturn('feeType')
            ->shouldReceive('getRefdataReference')
            ->with('CONT')
            ->andReturn($mockFeeType)
            ->once()
            ->shouldReceive('getRefdataReference')
            ->with('lcat_gv')
            ->andReturn($mockOperatorType)
            ->once()
            ->shouldReceive('getRefdataReference')
            ->with('ltyp_si')
            ->andReturn($mockLicenceType)
            ->once();

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => ['feeType'],
            'count' => 1
         ];

        $this->assertEquals($expected, $result);
    }
}
