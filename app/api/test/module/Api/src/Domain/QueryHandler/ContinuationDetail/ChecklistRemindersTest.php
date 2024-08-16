<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @covers Dvsa\Olcs\Api\Domain\QueryHandler\ContinuationDetail\ChecklistReminders
 */
class ChecklistRemindersTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler\ContinuationDetail\ChecklistReminders();

        $this->mockRepo('ContinuationDetail', Repository\ContinuationDetail::class);

        parent::setUp();
    }

    /**
     * Test handleQuery
     */
    public function test(): void
    {
        $data = [
            'month' => 'unit_Month',
            'year' => 'unit_Year',
            'ids' => ['unit_Ids'],
        ];
        $query = Query\ContinuationDetail\ChecklistReminders::create($data);

        $reminder = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->andReturn(['unit_RepoResult', null, null])
            ->once()
            ->getMock();

        $reminders = new ArrayCollection();
        $reminders->add($reminder);

        $trafficAreas = ['A', 'B'];

        $userData = [
            'dataAccess' => [
                'trafficAreas' => $trafficAreas,
            ],
        ];

        $this->expectedUserDataCacheCall($userData);

        $this->repoMap['ContinuationDetail']
            ->expects('fetchChecklistReminders')
            ->with($trafficAreas, 'unit_Month', 'unit_Year', ['unit_Ids'])
            ->andReturn($reminders);

        $actual = $this->sut->handleQuery($query);

        static::assertEquals(
            [
                'result' => [['unit_RepoResult', null, null]],
                'count' => 1,
            ],
            $actual
        );
    }
}
