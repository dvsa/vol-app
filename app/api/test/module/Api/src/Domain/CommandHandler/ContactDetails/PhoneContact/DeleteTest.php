<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ContactDetails\PhoneContact;

use Dvsa\Olcs\Api\Domain\CommandHandler\ContactDetails\PhoneContact\Delete as Handler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\ContactDetail\PhoneContact\Delete as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\ContactDetails\PhoneContact\Delete
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler
 */
class DeleteTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Handler();
        $this->mockRepo('PhoneContact', Repository\PhoneContact::class);

        parent::setUp();
    }

    public function test(): void
    {
        $id = 99999;

        $command = Cmd::create(['id' => $id]);

        $mockEntity = m::mock(Entity\ContactDetails\PhoneContact::class);

        $this->repoMap['PhoneContact']
            ->shouldReceive('fetchById')->with($id)->once()->andReturn($mockEntity)
            ->shouldReceive('delete')->once()->with($mockEntity)
            ->getMock();

        $actual = $this->sut->handleCommand($command);

        static::assertEquals(['id' . $id => $id], $actual->getIds());
        static::assertEquals(['Id ' . $id . ' deleted'], $actual->getMessages());
    }
}
