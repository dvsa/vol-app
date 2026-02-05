<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\DataRetention;

use Dvsa\Olcs\Api\Domain\CommandHandler\DataRetention\UpdateActionConfirmation;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\DataRetention\DataRetention;
use Dvsa\Olcs\Transfer\Command\DataRetention\MarkForDelete;
use Dvsa\Olcs\Transfer\Command\DataRetention\MarkForReview;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Class UpdateActionConfirmationTest
 */
class UpdateActionConfirmationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateActionConfirmation();
        $this->mockRepo('DataRetention', Repository\DataRetention::class);

        parent::setUp();
    }

    public function testActionConfirmationIfStatusIsReview(): void
    {
        $command = MarkForReview::create(['ids' => [100]]);

        // Record with actionConfirmation set to 0
        // Should become a record with actionConfirmation set to 1
        $dataRetentionRecordCurrent = m::mock(DataRetention::class);
        $dataRetentionRecordCurrent->shouldReceive('markForReview')
            ->once();

        // Should become a record with actionConfirmation set to 1
        $this->repoMap['DataRetention']
            ->shouldReceive('fetchById')
            ->with(100)
            ->once()
            ->andReturn($dataRetentionRecordCurrent)
            ->shouldReceive('save')
            ->once()
            ->with($dataRetentionRecordCurrent);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '1 Data retention record(s) updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testActionConfirmationIfStatusIsDelete(): void
    {
        $command = MarkForDelete::create(['ids' => [100], 'status' => 'delete']);

        // Record with actionConfirmation set to 0
        // Should become a record with actionConfirmation set to 1
        $dataRetentionRecordCurrent = m::mock(DataRetention::class);
        $dataRetentionRecordCurrent->shouldReceive('markForDelete')
            ->once();

        // Should become a record with actionConfirmation set to 1
        $this->repoMap['DataRetention']
            ->shouldReceive('fetchById')
            ->with(100)
            ->once()
            ->andReturn($dataRetentionRecordCurrent)
            ->shouldReceive('save')
            ->once()
            ->with($dataRetentionRecordCurrent);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '1 Data retention record(s) updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testActionConfirmationReviewButCannotSetToTrue(): void
    {
        $command = MarkForReview::create(['ids' => [100], 'status' => 'review']);

        // Record with actionConfirmation set to 0
        // Should become a record with actionConfirmation set to 1
        $dataRetentionRecordCurrent = m::mock(DataRetention::class);
        $dataRetentionRecordCurrent->shouldReceive('markForReview');

        // Should become a record with actionConfirmation set to 1
        $this->repoMap['DataRetention']
            ->shouldReceive('fetchById')
            ->with(100)
            ->once()
            ->andReturn($dataRetentionRecordCurrent)
            ->shouldReceive('save')
            ->once()
            ->with($dataRetentionRecordCurrent);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '1 Data retention record(s) updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testActionConfirmationTrueButCannotDeleteSoResetToFalse(): void
    {
        $command = MarkForDelete::create(['ids' => [100], 'status' => 'delete']);

        // Record with actionConfirmation set to 0
        // Should become a record with actionConfirmation set to 1
        $dataRetentionRecordCurrent = m::mock(DataRetention::class);
        $dataRetentionRecordCurrent->shouldReceive('markForDelete')
            ->once();

        // Should become a record with actionConfirmation set to 1
        $this->repoMap['DataRetention']
            ->shouldReceive('fetchById')
            ->with(100)
            ->once()
            ->andReturn($dataRetentionRecordCurrent)
            ->shouldReceive('save')
            ->once()
            ->with($dataRetentionRecordCurrent);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '1 Data retention record(s) updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testMultipleActionConfirmationToReview(): void
    {
        $command = MarkForReview::create(['ids' => [100, 200, 300], 'status' => 'review']);

        // Record with actionConfirmation set to 0
        // Should become a record with actionConfirmation set to 1
        $dataRetentionRecordCurrent = m::mock(DataRetention::class);
        $dataRetentionRecordCurrent->shouldReceive('markForReview')
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
