<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\DataRetention;

use Dvsa\Olcs\Api\Domain\Repository\DataRetentionRule as DataRetentionRuleRepo;
use Dvsa\Olcs\Api\Domain\QueryHandler\DataRetention\GetRule as QueryHandler;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Transfer\Query\DataRetention\GetRule as Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Mockery as m;

/**
 * Get Rule Test
 */
class GetRuleTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('DataRetentionRule', DataRetentionRuleRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 9999]);

        $mockEntity = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn(['unit_Result'])
            ->getMock();

        $this->repoMap['DataRetentionRule']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockEntity);

        /** @var Result $actual */
        $actual = $this->sut->handleQuery($query);

        static::assertSame(
            ['unit_Result'],
            $actual->serialize()
        );
    }
}
