<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TaskAlphaSplit;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\TaskAlphaSplit\Update as CommandHandler;
use Dvsa\Olcs\Transfer\Command\TaskAlphaSplit\Update as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;

/**
 * TaskAllocationRule UpdateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('TaskAlphaSplit', \Dvsa\Olcs\Api\Domain\Repository\TaskAlphaSplit::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            UserEntity::class => [
                1 => m::mock(UserEntity::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandAllParams(): void
    {
        $command = Cmd::create(
            [
                'id' => 1304,
                'version' => 87,
                'user' => 1,
                'letters' => 'abc',
            ]
        );

        $tas = new \Dvsa\Olcs\Api\Entity\Task\TaskAlphaSplit();
        $tas->setId(1304);

        $this->repoMap['TaskAlphaSplit']->shouldReceive('fetchUsingId')
            ->with($command, \Doctrine\ORM\Query::HYDRATE_OBJECT, 87)->once()->andReturn($tas);

        $this->repoMap['TaskAlphaSplit']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Task\TaskAlphaSplit $tas) {
                $this->assertSame($this->references[UserEntity::class][1], $tas->getUser());
                $this->assertSame('abc', $tas->getLetters());
                $this->assertSame(null, $tas->getTaskAllocationRule());
            }
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'id' => [
                    'task-alpha-split' => 1304,
                ],
                'messages' => [
                    'TaskAlphaSplit updated',
                ]
            ],
            $result->toArray()
        );
    }
}
