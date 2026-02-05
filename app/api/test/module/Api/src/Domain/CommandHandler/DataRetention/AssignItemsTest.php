<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\DataRetention;

use Dvsa\Olcs\Transfer\Command\DataRetention\AssignItems as AssignItemsCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\DataRetention\AssignItems;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\DataRetention\DataRetention;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Class AssignItemsTest
 */
class AssignItemsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new AssignItems();
        $this->mockRepo('DataRetention', Repository\DataRetention::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $user = m::mock(UserEntity::class);

        $this->references = [
            UserEntity::class => [
                999 => $user
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $id1 = 100;
        $id2 = 200;
        $id3 = 300;
        $user = 999;

        $command = AssignItemsCommand::create(
            [
                'ids' => [
                    $id1,
                    $id2,
                    $id3
                ],
                'user' => $user
            ]
        );

        $userEntity = $this->references[UserEntity::class][$user];
        $userEntity->shouldReceive('canBeAssignedDataRetention')
            ->once()
            ->withNoArgs()
            ->andReturn(true);

        $dataRetentionRecord1 = m::mock(DataRetention::class);
        $dataRetentionRecord1->shouldReceive('setAssignedTo')->once()->with($userEntity);

        $dataRetentionRecord2 = m::mock(DataRetention::class);
        $dataRetentionRecord2->shouldReceive('setAssignedTo')->once()->with($userEntity);

        $dataRetentionRecord3 = m::mock(DataRetention::class);
        $dataRetentionRecord3->shouldReceive('setAssignedTo')->once()->with($userEntity);

        $this->repoMap['DataRetention']
            ->shouldReceive('fetchById')
            ->with($id1)
            ->once()
            ->andReturn($dataRetentionRecord1);

        $this->repoMap['DataRetention']
            ->shouldReceive('fetchById')
            ->with($id2)
            ->once()
            ->andReturn($dataRetentionRecord2);

        $this->repoMap['DataRetention']
            ->shouldReceive('fetchById')
            ->with($id3)
            ->once()
            ->andReturn($dataRetentionRecord3);

        $this->repoMap['DataRetention']->shouldReceive('save')
            ->once()
            ->with($dataRetentionRecord1);

        $this->repoMap['DataRetention']->shouldReceive('save')
            ->once()
            ->with($dataRetentionRecord2);

        $this->repoMap['DataRetention']->shouldReceive('save')
            ->once()
            ->with($dataRetentionRecord3);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '3 Data retention record(s) updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandUserNotAllowed(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('can\'t assign data retention record to this user');

        $user = 999;

        $command = AssignItemsCommand::create(
            [
                'ids' => [],
                'user' => $user
            ]
        );

        $userEntity = $this->references[UserEntity::class][$user];
        $userEntity->shouldReceive('canBeAssignedDataRetention')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $this->sut->handleCommand($command);
    }
}
