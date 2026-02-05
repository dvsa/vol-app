<?php

declare(strict_types=1);

/**
 * Close Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Pi;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Pi\Close;
use Dvsa\Olcs\Api\Domain\Repository\Pi as PiRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\Close as CloseCmd;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;

/**
 * Close
 */
class CloseTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Close();
        $this->mockRepo('Pi', PiRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $case = 24;
        $pi = 6;

        $command = CloseCmd::Create(['id' => $case]);

        $mockPi = m::mock(PiEntity::class);
        $mockPi->shouldReceive('close')->once()->andReturnSelf();
        $mockPi->shouldReceive('getId')->once()->andReturn($pi);

        $this->repoMap['Pi']
            ->shouldReceive('fetchUsingCase')
            ->with($command, Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn($mockPi)
            ->shouldReceive('save')
            ->with(m::type(PiEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }
}
