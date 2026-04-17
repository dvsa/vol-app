<?php

declare(strict_types=1);

/**
 * Unlicensed Operator Business Details Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Operator;

use Dvsa\Olcs\Api\Domain\QueryHandler\Operator\UnlicensedBusinessDetails as BusinessDetailsQueryHandler;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Transfer\Query\Operator\BusinessDetails as Qry;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Mockery as m;

/**
 * Unlicensed Operator Business Details Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UnlicensedBusinessDetailsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = m::mock(BusinessDetailsQueryHandler::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->mockRepo('Organisation', OrganisationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn('organisation')
            ->once();

        $this->sut->shouldReceive('result')
            ->with(
                'organisation',
                [
                    'licences' => [
                        'correspondenceCd' => [
                            'address' => [
                                'countryCode',
                            ],
                            'phoneContacts' => [
                                'phoneContactType',
                            ],
                        ],
                        'goodsOrPsv',
                        'trafficArea',
                    ],
                ]
            )
            ->andReturn('result')
            ->once()
            ->getMock();

        $result = $this->sut->handleQuery($query);

        $this->assertEquals('result', $result);
    }
}
