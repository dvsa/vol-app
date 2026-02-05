<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\System\InfoMessage;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\System\InfoMessage\Update as Handler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\System\InfoMessage\Update as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\System\InfoMessage\Update
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Handler();
        $this->mockRepo('SystemInfoMessage', Repository\SystemInfoMessage::class);

        parent::setUp();
    }

    public function test(): void
    {
        $id = 99999;
        $startDate = (new \DateTime())->setTime(0, 0, 0);
        $endDate = (new \DateTime())->setTime(23, 59, 59);

        $data = [
            'description' => 'unit_Desc',
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
            'isInternal' => 'Y',
        ];
        $command = Cmd::create($data);

        $mockEntity = m::mock(Entity\System\SystemInfoMessage::class)
            ->shouldReceive('setDescription')
            ->once()
            ->with('unit_Desc')
            ->andReturnSelf()
            //
            ->shouldReceive('setStartDate')
            ->with(m::mustBe($startDate))
            ->andReturnSelf()
            //
            ->shouldReceive('setEndDate')
            ->with(m::mustBe($endDate))
            ->andReturnSelf()
            //
            ->shouldReceive('setIsInternal')
            ->with(true)
            ->andReturnSelf()
            //
            ->shouldReceive('getId')->andReturn($id)
            ->getMock();

        $this->repoMap['SystemInfoMessage']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn($mockEntity)
            //
            ->shouldReceive('save')
            ->once()
            ->with($mockEntity)
            ->getMock();

        $actual = $this->sut->handleCommand($command);

        static::assertEquals(['System Info Message \'' . $id . '\' updated'], $actual->getMessages());
    }
}
