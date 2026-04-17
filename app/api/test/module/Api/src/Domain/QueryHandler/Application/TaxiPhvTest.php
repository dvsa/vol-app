<?php

declare(strict_types=1);

/**
 * TaxiPhvTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Application\TaxiPhv as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Application\TaxiPhv as Query;

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
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('TrafficArea', \Dvsa\Olcs\Api\Domain\Repository\TrafficArea::class);
        $this->mockRepo('Licence', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 121]);

        $application = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class);
        $application->shouldReceive('getLicence->getId')->with()->once()->andReturn(44);
        $application->shouldReceive('serialize')->with(
            [
                'licence' => [
                    'trafficArea',
                    'privateHireLicences' => [
                        'contactDetails' => [
                            'address' => [
                                'countryCode'
                            ]
                        ]
                    ]
                ]
            ]
        )->once()->andReturn(['foobar']);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($query)->once()->andReturn($application);
        $this->repoMap['Licence']->shouldReceive('fetchWithPrivateHireLicence')->with(44)->once()->andReturn('STUFF');
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
