<?php

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

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand()
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

        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchById')
            ->with(99)
            ->andReturn($conditionUndertaking)
            ->once()
            ->shouldReceive('delete')
            ->with(m::type(ConditionUndertakingEntity::class))
            ->andReturnUsing(
                function (ConditionUndertakingEntity $conditionUndertaking) {
                    $conditionUndertaking->setId(99);
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Id 99 deleted', $result->getMessages());
    }
}
