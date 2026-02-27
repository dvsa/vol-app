<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TaskAlphaSplit;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\TaskAlphaSplit\Create as CommandHandler;
use Dvsa\Olcs\Transfer\Command\TaskAlphaSplit\Create as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule as TaskAllocationRuleEntity;

/**
 * TaskAllocationRule CreateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('TaskAlphaSplit', \Dvsa\Olcs\Api\Domain\Repository\TaskAlphaSplit::class);
        $this->mockRepo('TaskAllocationRule', \Dvsa\Olcs\Api\Domain\Repository\TaskAllocationRule::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            UserEntity::class => [
                1 => m::mock(UserEntity::class)
            ],
            TaskAllocationRuleEntity::class => [
                2 => m::mock(TaskAllocationRuleEntity::class)->makePartial()->setUser('USER')
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandAllParams(): void
    {
        $command = Cmd::create(
            [
                'user' => 1,
                'taskAllocationRule' => 2,
                'letters' => 'abc',
            ]
        );

        $this->repoMap['TaskAlphaSplit']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Task\TaskAlphaSplit $tas) {
                $this->assertSame($this->references[UserEntity::class][1], $tas->getUser());
                $this->assertSame('abc', $tas->getLetters());
                $this->assertSame($this->references[TaskAllocationRuleEntity::class][2], $tas->getTaskAllocationRule());
                $tas->setId(1304);
            }
        );

        $this->repoMap['TaskAllocationRule']->shouldReceive('save')->once()->andReturnUsing(
            function (TaskAllocationRuleEntity $tar) {
                $this->assertSame(null, $tar->getUser());
            }
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'id' => [
                    'task-alpha-split' => 1304,
                ],
                'messages' => [
                    'TaskAlphaSplit created',
                ]
            ],
            $result->toArray()
        );
    }
}
