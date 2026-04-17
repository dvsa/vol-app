<?php

declare(strict_types=1);

/**
 * TaxiPhvTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\TaxiPhv as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Licence\TaxiPhv as Query;
use Dvsa\Olcs\Api\Entity\Licence\Licence as Licence;

/**
 * TaxiPhvTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TaxiPhvTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Licence', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);
        $this->mockRepo('TrafficArea', \Dvsa\Olcs\Api\Domain\Repository\TrafficArea::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 121]);

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('serialize')->with(
            [
                'trafficArea',
                'privateHireLicences' => [
                    'contactDetails' => [
                        'address' => [
                            'countryCode'
                        ]
                    ]
                ]
            ]
        )->once()->andReturn(['foobar']);

        $this->repoMap['Licence']->shouldReceive('fetchWithPrivateHireLicence')->with(121)->once()->andReturn($licence);
        $this->repoMap['TrafficArea']->shouldReceive('getValueOptions')->with()->once()->andReturn(['OPTIONS']);

        $response = $this->sut->handleQuery($query);
        $this->assertSame(
            [
                'foobar',
                'trafficAreaOptions' => ['OPTIONS'],
            ],
            $response->serialize()
        );
    }
}
