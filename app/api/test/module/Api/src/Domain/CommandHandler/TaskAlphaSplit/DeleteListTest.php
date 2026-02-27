<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TaskAlphaSplit;

use Dvsa\Olcs\Api\Domain\CommandHandler\TaskAlphaSplit\DeleteList as CommandHandler;
use Dvsa\Olcs\Transfer\Command\TaskAlphaSplit\DeleteList as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * TaskAlphaSplit DeleteListTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeleteListTest extends AbstractCommandHandlerTestCase
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
        parent::initReferences();
    }

    public function testHandleCommandAllParams(): void
    {
        $command = Cmd::create(
            [
                'ids' => [1304, 2016],
            ]
        );

        $tas1 = new \Dvsa\Olcs\Api\Entity\Task\TaskAlphaSplit();
        $tas1->setId(1304);
        $tas2 = new \Dvsa\Olcs\Api\Entity\Task\TaskAlphaSplit();
        $tas2->setId(2016);

        $this->repoMap['TaskAlphaSplit']->shouldReceive('fetchByIds')
            ->with([1304, 2016])->once()->andReturn([$tas1, $tas2]);

        $this->repoMap['TaskAlphaSplit']->shouldReceive('delete')->with($tas1)->once();
        $this->repoMap['TaskAlphaSplit']->shouldReceive('delete')->with($tas2)->once();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'id' => [
                ],
                'messages' => [
                    'Task Alpha Split ID 1304 deleted',
                    'Task Alpha Split ID 2016 deleted',
                ]
            ],
            $result->toArray()
        );
    }
}
