<?php

declare(strict_types=1);

/**
 * Refuse BusReg Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\RefuseBusReg;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Bus\RefuseBusReg as Cmd;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrRefused;

/**
 * Refuse BusReg Test
 */
class RefuseBusRegTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new RefuseBusReg();
        $this->mockRepo('Bus', BusRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            BusRegEntity::STATUS_REFUSED
        ];

        parent::initReferences();
    }

    /**
     * test handleCommand when Ebsr
     */
    public function testHandleCommandEbsr(): void
    {
        $id = 99;
        $ebsrId = 55;

        $command = Cmd::Create(
            [
                'id' => $id,
            ]
        );

        /** @var BusRegEntity $busReg */
        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('refuse')->once();
        $busReg->shouldReceive('getId')->andReturn($id);
        $busReg->shouldReceive('isFromEbsr')->once()->andReturn(true);
        $busReg->shouldReceive('getEbsrSubmissions->first->getId')->once()->andReturn($ebsrId);
        $this->expectedEmailQueueSideEffect(SendEbsrRefused::class, ['id' => $ebsrId], $ebsrId, new Result());

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($busReg)
            ->shouldReceive('save')
            ->with(m::type(BusRegEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * test handleCommand when not Ebsr
     */
    public function testHandleCommandNotEbsr(): void
    {
        $id = 99;

        $command = Cmd::Create(
            [
                'id' => $id,
            ]
        );

        /** @var BusRegEntity $busReg */
        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('refuse')->once();
        $busReg->shouldReceive('getId')->andReturn($id);
        $busReg->shouldReceive('isFromEbsr')->once()->andReturn(false);
        $busReg->shouldReceive('getEbsrSubmissions->first->getId')->never();

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($busReg)
            ->shouldReceive('save')
            ->with(m::type(BusRegEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }
}
