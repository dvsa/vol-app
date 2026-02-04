<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ConditionUndertaking;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\ConditionUndertaking\Update as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as ConditionUndertakingRepo;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Update as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;

/**
 * UpdateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ConditionUndertaking', ConditionUndertakingRepo::class);
        $this->mockedSmServices ['EventHistoryCreator'] = m::mock(EventHistoryCreator::class);

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [
            'TYPE',
            'ATTACHED_TO',
            'cu_cat_other',
        ];

        $this->references = [
            OperatingCentre::class => [
                32 => m::mock(OperatingCentre::class),
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandFailValidation()
    {
        $data = [
            'attachedTo' => 'cat_oc',
        ];
        $command = Command::create($data);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommand()
    {
        $data = [
            'id' => 154,
            'version' => 43,
            'type' => 'TYPE',
            'attachedTo' => 'ATTACHED_TO',
            'fulfilled' => 'Y',
            'notes' => 'FooBar',
            'conditionCategory' => 'cu_cat_other',
        ];
        $command = Command::create($data);

        $mockConditionUndertaking = m::mock(ConditionUndertaking::class)->makePartial();
        $mockConditionUndertaking->setId(154);
        $mockConditionUndertaking->setApplication(99);

        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchById')->with(154, Query::HYDRATE_OBJECT, 43)->once()
            ->andReturn($mockConditionUndertaking);

        $this->repoMap['ConditionUndertaking']->shouldReceive('save')->once()->andReturnUsing(
            function (ConditionUndertaking $cu) use ($data) {
                $this->assertSame($this->refData[$data['type']], $cu->getConditionType());
                $this->assertSame($this->refData[$data['conditionCategory']], $cu->getConditionCategory());
                $this->assertSame($this->refData[$data['attachedTo']], $cu->getAttachedTo());
                $this->assertSame($data['fulfilled'], $cu->getIsFulfilled());
                $this->assertSame($data['notes'], $cu->getNotes());
                $this->assertSame(null, $cu->getOperatingCentre());
            }
        );

        $eventHistoryType = $command->getType() === ConditionUndertaking::TYPE_CONDITION ? 
            EventHistoryTypeEntity::EVENT_CODE_CONDITION_CHANGED : EventHistoryTypeEntity::EVENT_CODE_UNDERTAKING_CHANGED;

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with($mockConditionUndertaking, $eventHistoryType)
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['ConditionUndertaking updated'], $result->getMessages());
        $this->assertEquals(['conditionUndertaking' => 154], $result->getIds());
    }

    public function testHandleCommandWithOperatingCentre()
    {
        $data = [
            'id' => 154,
            'version' => 43,
            'type' => 'TYPE',
            'attachedTo' => 'ATTACHED_TO',
            'fulfilled' => 'Y',
            'notes' => 'FooBar',
            'operatingCentre' => 32,
            'conditionCategory' => 'cu_cat_other',
        ];
        $command = Command::create($data);

        $mockConditionUndertaking = m::mock(ConditionUndertaking::class)->makePartial();
        $mockConditionUndertaking->setId(154);
        $mockConditionUndertaking->setApplication(99);

        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchById')->with(154, Query::HYDRATE_OBJECT, 43)->once()
            ->andReturn($mockConditionUndertaking);

        $this->repoMap['ConditionUndertaking']->shouldReceive('save')->once()->andReturnUsing(
            function (ConditionUndertaking $cu) use ($data) {
                $this->assertSame($this->refData[$data['type']], $cu->getConditionType());
                $this->assertSame($this->refData[$data['conditionCategory']], $cu->getConditionCategory());
                $this->assertSame($this->refData[$data['attachedTo']], $cu->getAttachedTo());
                $this->assertSame($data['fulfilled'], $cu->getIsFulfilled());
                $this->assertSame($data['notes'], $cu->getNotes());
                $this->assertSame(
                    $this->references[OperatingCentre::class][32],
                    $cu->getOperatingCentre()
                );
            }
        );

        $eventHistoryType = $command->getType() === ConditionUndertaking::TYPE_CONDITION ? 
            EventHistoryTypeEntity::EVENT_CODE_CONDITION_CHANGED : EventHistoryTypeEntity::EVENT_CODE_UNDERTAKING_CHANGED;

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with($mockConditionUndertaking, $eventHistoryType)
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['ConditionUndertaking updated'], $result->getMessages());
        $this->assertEquals(['conditionUndertaking' => 154], $result->getIds());
    }
}
