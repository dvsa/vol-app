<?php

declare(strict_types=1);

/**
 * Partner Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\User;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\User\Partner;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Partner as PartnerRepo;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Transfer\Query\User\Partner as Qry;

/**
 * Partner Test
 */
class PartnerTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Partner();
        $this->mockRepo('Partner', PartnerRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);

        $this->repoMap['Partner']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn(
                m::mock(BundleSerializableInterface::class)
                    ->shouldReceive('serialize')
                    ->andReturn(['foo'])
                    ->getMock()
            );

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(['foo'], $result->serialize());
    }
}
