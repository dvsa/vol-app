<?php

declare(strict_types=1);

/**
 * Update Short Notice Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\UpdateShortNotice;
use Dvsa\Olcs\Api\Domain\Repository\BusShortNotice as BusShortNoticeRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateShortNotice as Cmd;
use Dvsa\Olcs\Api\Entity\Bus\BusShortNotice as ShortNoticeEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Update Short Notice Test
 */
class UpdateShortNoticeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateShortNotice();
        $this->mockRepo('BusShortNotice', BusShortNoticeRepo::class);

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

        /** @var ShortNoticeEntity $busReg */
        $shortNotice = m::mock(ShortNoticeEntity::class);
        $shortNotice->shouldReceive('update')
            ->once()
            ->shouldReceive('getId')
            ->andReturn($id);

        $this->repoMap['BusShortNotice']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $command->getVersion())
            ->andReturn($shortNotice)
            ->shouldReceive('save')
            ->with(m::type(ShortNoticeEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }
}
