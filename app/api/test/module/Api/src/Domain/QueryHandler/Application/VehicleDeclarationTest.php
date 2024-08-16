<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\VehicleDeclaration as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Application\VehicleDeclaration as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

/**
 * VehicleDeclarationTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class VehicleDeclarationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        parent::setUp();
    }

    public function testHandleQuery()
    {
        $query = Query::create(['id' => 1066]);

        $mock = \Mockery::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);
        $mock->shouldReceive('serialize')->with(
            [
                'licence' => [
                    'trafficArea'
                ]
            ]
        )->once()->andReturn(['RESULT']);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($query)->once()->andReturn($mock);

        $this->assertSame(['RESULT'], $this->sut->handleQuery($query)->serialize());
    }
}
