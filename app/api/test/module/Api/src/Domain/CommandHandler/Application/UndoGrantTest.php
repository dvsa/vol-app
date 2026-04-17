<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UndoGrant;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Application\UndoGrant as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Application\UndoCancelAllInterimFees as UndoCmd;

/**
 * Grant Goods Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UndoGrantTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UndoGrant();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('Task', \Dvsa\Olcs\Api\Domain\Repository\Task::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            Licence::LICENCE_STATUS_UNDER_CONSIDERATION,
            ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION
        ];
        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111
        ];

        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        /** @var Task $task */
        $task = m::mock(Task::class)->makePartial();

        $tasks = new ArrayCollection();
        $tasks->add($task);

        /** @var Fee $fee */
        $fee = m::mock(Fee::class)
            ->shouldReceive('isGrantFee')
            ->andReturn(true)
            ->shouldReceive('isOutstanding')
            ->andReturn(true)
            ->shouldReceive('isFullyOutstanding')
            ->andReturn(true)
            ->shouldReceive('getId')
            ->andReturn(333)
            ->getMock();

        $fees = new ArrayCollection();
        $fees->add($fee);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setLicence($licence);
        $application
            ->shouldReceive('getTasks->matching')
            ->andReturn($tasks);
        $application
            ->shouldReceive('getFees')
            ->andReturn($fees);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $this->repoMap['Task']->shouldReceive('save')
            ->once()
            ->with($task);

        $result1 = new Result();
        $result1->addMessage('CancelLicenceFees');
        $this->expectedSideEffect(CancelFee::class, ['id' => 333], $result1);

        $result2 = new Result();
        $result2->addMessage('UndoCancelAllInterimFees');
        $this->expectedSideEffect(UndoCmd::class, ['id' => 111], $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'CancelLicenceFees',
                'UndoCancelAllInterimFees',
                '1 Task(s) closed',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(Licence::LICENCE_STATUS_UNDER_CONSIDERATION, $licence->getStatus()->getId());
        $this->assertEquals(
            ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION,
            $application->getStatus()->getId()
        );

        $this->assertEquals('Y', $task->getIsClosed());
    }
}
