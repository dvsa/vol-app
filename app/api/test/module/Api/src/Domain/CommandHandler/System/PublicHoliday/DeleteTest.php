<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\System\PublicHoliday;

use Dvsa\Olcs\Api\Domain\CommandHandler\System\PublicHoliday\Delete as Handler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\System\PublicHoliday\Delete as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\System\PublicHoliday\Delete
 */
class DeleteTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Handler();
        $this->mockRepo('PublicHoliday', Repository\PublicHoliday::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 99999;

        $command = Cmd::create(['id' => $id]);

        $mockEntity = m::mock(Entity\System\PublicHoliday::class);

        $this->repoMap['PublicHoliday']
            ->shouldReceive('fetchById')->with($id)->once()->andReturn($mockEntity)
            ->shouldReceive('delete')->once()->with($mockEntity)
            ->getMock();

        $actual = $this->sut->handleCommand($command);

        static::assertEquals(['id' . $id => $id], $actual->getIds());
        static::assertEquals(['Id ' . $id . ' deleted'], $actual->getMessages());
    }
}
