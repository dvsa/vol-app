<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\System\PublicHoliday;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\System\PublicHoliday\Update as Handler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\System\PublicHoliday\Update as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\System\PublicHoliday\Update
 */
class UpdateTest extends AbstractCommandHandlerTestCase
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
        $holidayDate = (new \DateTime())->format('Y-m-d');

        $data = [
            'holidayDate' => $holidayDate,
            'isEngland' => 'Y',
            'isWales' => 'N',
            'isScotland' => 'Y',
            'isNi' => 'N',
        ];
        $command = Cmd::create($data);

        $mockEntity = m::mock(Entity\System\PublicHoliday::class)->makePartial()
            ->shouldReceive('getId')->andReturn($id)
            ->getMock();

        $this->repoMap['PublicHoliday']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn($mockEntity)
            //
            ->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (Entity\System\PublicHoliday $entity) use ($holidayDate, $id) {
                    static::assertEquals($entity->getPublicHolidayDate(), new DateTime($holidayDate));
                    static::assertEquals($entity->getIsEngland(), 'Y');
                    static::assertEquals($entity->getIsWales(), 'N');
                    static::assertEquals($entity->getIsScotland(), 'Y');
                    static::assertEquals($entity->getIsNi(), 'N');

                    return $entity;
                }
            )
            ->getMock();

        $actual = $this->sut->handleCommand($command);

        static::assertEquals(['Public Holiday \'' . $id . '\' updated'], $actual->getMessages());
    }
}
