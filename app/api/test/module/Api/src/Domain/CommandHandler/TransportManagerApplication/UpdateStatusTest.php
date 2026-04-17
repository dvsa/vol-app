<?php

declare(strict_types=1);

/**
 * UpdateStatusTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication\UpdateStatus as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\UpdateStatus as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication as TmaEntity;
use Mockery as m;

/**
 * UpdateStatusTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class UpdateStatusTest extends AbstractCommandHandlerTestCase
{
    protected $loggedInUser;

    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('TransportManagerApplication', TransportManagerApplication::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = ['status1',TmaEntity::STATUS_INCOMPLETE];

        parent::initReferences();
    }

    public function testHandleCommandWithVersion(): void
    {
        $command = Command::create(['id' => 863, 'version' => 234, 'status' => 'status1']);

        $tma = new TmaEntity();

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')->once()
            ->with($command, \Doctrine\ORM\Query::HYDRATE_OBJECT, 234)->andReturn($tma);
        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once()->andReturnUsing(
            function (TmaEntity $tma) {
                $this->assertSame($this->refData['status1'], $tma->getTmApplicationStatus());
            }
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithoutVersion(): void
    {
        $command = Command::create(['id' => 863, 'status' => 'status1']);

        $tma = new TmaEntity();

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')->once()
            ->with($command)->andReturn($tma);
        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once()->andReturnUsing(
            function (TmaEntity $tma) {
                $this->assertSame($this->refData['status1'], $tma->getTmApplicationStatus());
            }
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandStatusIncomplete(): void
    {
        $command = Command::create(
            [
                'id' => 863,
                'status' => TmaEntity::STATUS_INCOMPLETE
            ]
        );

        $tma = m::mock(TmaEntity::class);
        $tma->shouldReceive('getId')->once();
        $tma->shouldReceive('setTmApplicationStatus')->with($this->refData[TmaEntity::STATUS_INCOMPLETE])->once();
        $tma->shouldReceive('setOpDigitalSignature')->with(null)->once();
        $tma->shouldReceive('setOpSignatureType')->with(null)->once();
        $tma->shouldReceive('setTmDigitalSignature')->with(null)->once();
        $tma->shouldReceive('setTmSignatureType')->with(null)->once();

        $this->repoMap['TransportManagerApplication']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($tma);

        $this->repoMap['TransportManagerApplication']
            ->shouldReceive('save')
            ->once()
            ->with($tma);

        $this->sut->handleCommand($command);
    }
}
