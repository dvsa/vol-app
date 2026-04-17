<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\System\PublicHoliday;

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\QueryHandler\System\PublicHoliday\Get
 */
class GetTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler\System\PublicHoliday\Get();
        $this->mockRepo('PublicHoliday', Repository\PublicHoliday::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query\System\PublicHoliday\Get::create(['id' => 9999]);

        $mockEntity = m::mock(QueryHandler\BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->with([])
            ->once()
            ->andReturn(['unit_Result'])
            ->getMock();

        $this->repoMap['PublicHoliday']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockEntity);

        /** @var QueryHandler\Result $actual */
        $actual = $this->sut->handleQuery($query);

        static::assertSame(
            ['unit_Result'],
            $actual->serialize()
        );
    }
}
