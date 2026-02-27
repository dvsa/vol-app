<?php

declare(strict_types=1);

/**
 * Delete ConditionUndertaking Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ConditionUndertaking;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\ConditionUndertaking\Delete;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Delete as Cmd;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as ConditionUndertakingEntity;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Delete ConditionUndertaking Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class DeleteTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Delete();
        $this->mockRepo('ConditionUndertaking', ConditionUndertaking::class);
        $this->mockedSmServices ['EventHistoryCreator'] = m::mock(EventHistoryCreator::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::Create(
            [
                'id' => 99,
                'version' => 1
            ]
        );

        /** @var ConditionUndertakingEntity $conditionUndertaking */
        $conditionUndertaking = m::mock(ConditionUndertakingEntity::class)->makePartial();
        $conditionUndertaking->setId($command->getId());

        $refData = m::mock(RefData::class);
            $refData->shouldReceive('getId')
            ->andReturn(ConditionUndertakingEntity::TYPE_CONDITION);

        $conditionUndertaking->shouldReceive('getConditionType')
            ->andReturn($refData);

        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($conditionUndertaking)
            ->twice();

        $this->repoMap['ConditionUndertaking']
            ->shouldReceive('delete')
            ->with($conditionUndertaking)
            ->once();

        $this->mockedSmServices['EventHistoryCreator']
            ->shouldReceive('create')
            ->with(
                $conditionUndertaking,
                EventHistoryTypeEntity::EVENT_CODE_CONDITION_DELETED
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Id 99 deleted', $result->getMessages());
    }
}
