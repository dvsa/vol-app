<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\DataRetention;

use Dvsa\Olcs\Api\Domain\QueryHandler\DataRetention\GetProcessedList;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Transfer\Query\DataRetention\GetProcessedList as Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;

/**
 * Class GetProcessedListTest
 */
class GetProcessedListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GetProcessedList();
        $this->mockRepo('DataRetention', Repository\DataRetention::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['dataRetentionRuleId' => 9999, 'startDate' => '2017-05-23', 'endDate' => '2017-09-01']);

        $this->repoMap['DataRetention']->shouldReceive('fetchAllProcessedForRule')
            ->once()
            ->andReturnUsing(function ($id, $startDate, $endDate) {
                $this->assertEquals(9999, $id);
                $this->assertEquals('2017-05-23', $startDate->format('Y-m-d'));
                $this->assertEquals('2017-09-01', $endDate->format('Y-m-d'));
                return ['RESULTS'];
            });

        /** @var Result $actual */
        $actual = $this->sut->handleQuery($query);

        $this->assertSame(['count' => 1, 'result' => ['RESULTS']], $actual);
    }
}
