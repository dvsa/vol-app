<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TaskAllocationRule;

use Dvsa\Olcs\Api\Domain\CommandHandler\TaskAllocationRule\DeleteList as CommandHandler;
use Dvsa\Olcs\Transfer\Command\TaskAllocationRule\DeleteList as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * TaskAllocationRule DeleteListTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeleteListTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('TaskAllocationRule', \Dvsa\Olcs\Api\Domain\Repository\TaskAllocationRule::class);
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
        $tas2 = new \Dvsa\Olcs\Api\Entity\Task\TaskAlphaSplit();

        $tar1 = new \Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule();
        $tar1->setId(1304);
        $tar1->addTaskAlphaSplits($tas1);
        $tar1->addTaskAlphaSplits($tas2);
        $tar2 = new \Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule();
        $tar2->setId(2016);

        $this->repoMap['TaskAllocationRule']->shouldReceive('fetchByIds')
            ->with([1304, 2016])->once()->andReturn([$tar1, $tar2]);

        $this->repoMap['TaskAlphaSplit']->shouldReceive('delete')->with($tas1)->once();
        $this->repoMap['TaskAlphaSplit']->shouldReceive('delete')->with($tas2)->once();

        $this->repoMap['TaskAllocationRule']->shouldReceive('delete')->with($tar1)->once();
        $this->repoMap['TaskAllocationRule']->shouldReceive('delete')->with($tar2)->once();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'id' => [
                ],
                'messages' => [
                    'Task Allocation Rule ID 1304 deleted',
                    'Task Allocation Rule ID 2016 deleted',
                ]
            ],
            $result->toArray()
        );
    }
}
