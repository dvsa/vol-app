<?php

/**
 * Workshop Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Workshop;

use Dvsa\Olcs\Api\Domain\QueryHandler\Workshop\Workshop;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Workshop as WorkshopRepo;
use Dvsa\Olcs\Transfer\Query\Workshop\Workshop as Qry;
use Mockery as m;

/**
 * Workshop Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class WorkshopTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Workshop();
        $this->mockRepo('Workshop', WorkshopRepo::class);

        parent::setUp();
    }

    public function testHandleQuery()
    {
        $query = Qry::create(['id' => 111]);

        $mock = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);
        $mock->shouldReceive('serialize')->with(
            [
                'contactDetails' => [
                    'address' => ['countryCode']
                ]
            ]
        )->once()->andReturn(['foo']);

        $this->repoMap['Workshop']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mock);

        $this->assertEquals(['foo'], $this->sut->handleQuery($query)->serialize());
    }
}
