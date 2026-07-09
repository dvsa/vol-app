<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\System\InfoMessage;

use Dvsa\Olcs\Api\Domain\CommandHandler\System\InfoMessage\Create as Handler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\System\InfoMessage\Create as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\System\InfoMessage\Create
 */
final class CreateTest extends AbstractCommandHandlerTestCase
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
        $startDate = new \DateTime()->setTime(0, 0, 0);
        $endDate = new \DateTime()->setTime(23, 59, 59);

        $data = [
            'description' => 'unit_Desc',
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
            'isInternal' => 'Y',
        ];
        $command = Cmd::create($data);

        $this->repoMap['SystemInfoMessage']
            ->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (Entity\System\SystemInfoMessage $entity) use ($startDate, $endDate, $id) {
                    $this->assertEquals('unit_Desc', $entity->getDescription());
                    $this->assertEquals($entity->getStartDate(), $startDate);
                    $this->assertEquals($entity->getEndDate(), $endDate);
                    $this->assertEquals('Y', $entity->getIsInternal());

                    $entity->setId($id);
                }
            )
            ->getMock();

        $actual = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['systemInfoMessage' => $id],
            'messages' => ['System Info Message \'' . $id . '\' created'],
        ];
        $this->assertEquals($expected, $actual->toArray());
    }
}
