<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\EventHistory;

use Dvsa\Olcs\Api\Domain\QueryHandler\EventHistory\EventHistory as QueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\EventHistory as EventHistoryRepo;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistory as Entity;
use Dvsa\Olcs\Transfer\Query\EventHistory\EventHistory as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\QueryHandler\EventHistory\EventHistory
 */
class EventHistoryTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('EventHistory', EventHistoryRepo::class);

        parent::setUp();
    }

    /**
     * Test handle query
     *
     * @param array $eventHistoryDetails,
     * @param string $entityType
     * @param int $entityPk
     * @param int $entityVersion
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('eventHistoryDetailsProvider')]
    public function testHandleQuery(mixed $details, mixed $entityType, mixed $entityPk, mixed $entityVersion): void
    {
        $query = Qry::create(['id' => 69]);

        $mockEventHistory = m::mock(Entity::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn(
                [
                    'id' => 69,
                    'entityPk' => $entityPk,
                    'entityType' => $entityType,
                    'entityVersion' => $entityVersion,
                    'eventHistoryDetails' => $details
                ]
            )
            ->shouldReceive('getEntityPk')
            ->andReturn($entityPk)
            ->once()
            ->shouldReceive('getEntityType')
            ->andReturn($entityType)
            ->once()
            ->shouldReceive('getEntityVersion')
            ->andReturn($entityVersion)
            ->once()
            ->getMock();

        $this->repoMap['EventHistory']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockEventHistory)
            ->shouldReceive('fetchEventHistoryDetails')
            ->with($entityPk, $entityVersion, $entityType . '_hist')
            ->andReturn($details);

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $expected = [
            'id' => 69,
            'entityPk' => $entityPk,
            'entityType' => $entityType,
            'entityVersion' => $entityVersion,
            'eventHistoryDetails' => $details
        ];

        $this->assertEquals($expected, $result->serialize());
    }

    public static function eventHistoryDetailsProvider(): array
    {
        return [
            [['foo' => 'bar'], 'application', 1, 2],
            [[], 'application', 1, 2],
            [[], null, null, null]
        ];
    }

    public function testHandleQueryWithException(): void
    {
        $query = Qry::create(['id' => 69]);

        $mockEventHistory = m::mock(Entity::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn(
                [
                    'id' => 69,
                    'entityPk' => 1,
                    'entityType' => 'application',
                    'entityVersion' => 2,
                    'eventHistoryDetails' => []
                ]
            )
            ->shouldReceive('getEntityPk')
            ->andReturn(1)
            ->once()
            ->shouldReceive('getEntityType')
            ->andReturn('application')
            ->once()
            ->shouldReceive('getEntityVersion')
            ->andReturn(2)
            ->once()
            ->getMock();

        $this->repoMap['EventHistory']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockEventHistory)
            ->shouldReceive('fetchEventHistoryDetails')
            ->with(1, 2, 'application_hist')
            ->andThrow(\Exception::class);

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $expected = [
            'id' => 69,
            'entityPk' => 1,
            'entityType' => 'application',
            'entityVersion' => 2,
            'eventHistoryDetails' => []
        ];

        $this->assertEquals($expected, $result->serialize());
    }
}
