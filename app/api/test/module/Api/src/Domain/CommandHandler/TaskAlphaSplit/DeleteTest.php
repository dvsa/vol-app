<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TaskAlphaSplit;

use Dvsa\Olcs\Api\Domain\CommandHandler\TaskAlphaSplit\Delete as CommandHandler;
use Dvsa\Olcs\Transfer\Command\TaskAlphaSplit\Delete as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * TaskAlphaSplit DeleteTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeleteTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('TaskAlphaSplit', \Dvsa\Olcs\Api\Domain\Repository\TaskAlphaSplit::class);

        parent::setUp();
    }

    protected function initReferences()
    {
        parent::initReferences();
    }

    public function testHandleCommandAllParams()
    {
        $command = Cmd::create(
            [
                'id' => 1304,
            ]
        );

        $tas = new \Dvsa\Olcs\Api\Entity\Task\TaskAlphaSplit();
        $tas->setId(1304);

        $this->repoMap['TaskAlphaSplit']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($tas);
        $this->repoMap['TaskAlphaSplit']->shouldReceive('delete')->with($tas)->once();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'id' => [
                ],
                'messages' => [
                    'Task Alpha Split ID 1304 deleted',
                ]
            ],
            $result->toArray()
        );
    }
}
