<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\DataRetention;

use Dvsa\Olcs\Transfer\Command\DataRetention\DelayItems as DelayItemsCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\DataRetention\DelayItems;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\DataRetention\DataRetention;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Class DelayItemsTest
 */
class DelayItemsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DelayItems();
        $this->mockRepo('DataRetention', Repository\DataRetention::class);

        parent::setUp();
    }

    public function testDelayItemsForMultiple(): void
    {
        $command = DelayItemsCommand::create(
            [
                'ids' => [
                    100,
                    200,
                    300
                ],
                'nextReviewDate' => new \DateTime('2017-01-01')
            ]
        );

        // Record with actionConfirmation set to 0
        // Should become a record with actionConfirmation set to 1
        $dataRetentionRecordCurrent = m::mock(DataRetention::class);
        $dataRetentionRecordCurrent->shouldReceive('markForDelay')
            ->times(3);

        // Should become a record with actionConfirmation set to 1
        $this->repoMap['DataRetention']
            ->shouldReceive('fetchById')
            ->with(100)
            ->once()
            ->andReturn($dataRetentionRecordCurrent)
            ->shouldReceive('fetchById')
            ->with(200)
            ->once()
            ->andReturn($dataRetentionRecordCurrent)
            ->shouldReceive('fetchById')
            ->with(300)
            ->once()
            ->andReturn($dataRetentionRecordCurrent)
            ->shouldReceive('save')
            ->times(3)
            ->with($dataRetentionRecordCurrent);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '3 Data retention record(s) updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
