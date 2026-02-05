<?php

declare(strict_types=1);

/**
 * Update Stops Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\UpdateStops;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateStops as Cmd;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Update Stops Test
 */
class UpdateStopsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateStops();
        $this->mockRepo('Bus', BusRepo::class);

        parent::setUp();
    }

    /**
     * testHandleCommand
     */
    public function testHandleCommand(): void
    {
        $id = 99;

        $command = Cmd::Create(
            [
                'id' => $id,
            ]
        );

        /** @var BusEntity $busReg */
        $busReg = m::mock(BusEntity::class);
        $busReg->shouldReceive('updateStops')
            ->once()
            ->shouldReceive('getId')
            ->andReturn($id);

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $command->getVersion())
            ->andReturn($busReg)
            ->shouldReceive('save')
            ->with(m::type(BusEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }
}
