<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\System\PublicHoliday;

use Dvsa\Olcs\Api\Domain\CommandHandler\System\PublicHoliday\Create as Handler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\System\PublicHoliday\Create as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\System\PublicHoliday\Create
 */
class CreateTest extends AbstractCommandHandlerTestCase
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
        $holidayDate = (new \DateTime())->setTime(0, 0, 0);

        $data = [
            'holidayDate' => $holidayDate->format('Y-m-d'),
            'isEngland' => 'N',
            'isWales' => 'Y',
            'isScotland' => 'N',
            'isNi' => 'Y',
        ];
        $command = Cmd::create($data);

        $this->repoMap['PublicHoliday']
            ->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (Entity\System\PublicHoliday $entity) use ($holidayDate, $id) {
                    static::assertEquals($entity->getPublicHolidayDate(), $holidayDate);
                    static::assertEquals($entity->getIsEngland(), 'N');
                    static::assertEquals($entity->getIsWales(), 'Y');
                    static::assertEquals($entity->getIsScotland(), 'N');
                    static::assertEquals($entity->getIsNi(), 'Y');

                    $entity->setId($id);
                }
            )
            ->getMock();

        $actual = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['publicHoliday' => $id],
            'messages' => ['Public Holiday \'' . $id . '\' created'],
        ];
        static::assertEquals($expected, $actual->toArray());
    }
}
