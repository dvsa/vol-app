<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Application\OperatingCentre as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Application\OperatingCentre as Qry;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;

/**
 * OperatingCentreTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OperatingCentreTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $data = ['id' => 234];
        $query = Qry::create($data);

        $mockApplication = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class)
            ->shouldReceive('serialize')->with(
                [
                    'licence' => [
                        'operatingCentres' => [
                            'operatingCentre' => ['address']
                        ]
                    ],
                    'operatingCentres' => [
                        'operatingCentre' => ['address']
                    ],
                ]
            )->once()->andReturn(['FooBar'])->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($query)->once()->andReturn($mockApplication);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(['FooBar'], $result->serialize());
    }
}
