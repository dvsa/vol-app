<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\EnforcementArea as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Application\EnforcementArea as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;

/**
 * Enforcement Area Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class EnforcementAreaTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 1066]);

        $mock = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')->with(
                [
                    'licence' => [
                        'enforcementArea'
                    ]
                ]
            )
            ->once()
            ->andReturn(['foo'])
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mock);

        $this->assertSame(['foo'], $this->sut->handleQuery($query)->serialize());
    }
}
