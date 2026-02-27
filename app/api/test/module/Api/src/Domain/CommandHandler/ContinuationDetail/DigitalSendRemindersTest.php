<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\Command\Queue\Create;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail\DigitalSendReminders as CommandHandler;
use Dvsa\Olcs\Api\Domain\Command\ContinuationDetail\DigitalSendReminders as Command;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository;

/**
 * Class DigitalSendRemindersTest
 */
class DigitalSendRemindersTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ContinuationDetail', Repository\ContinuationDetail::class);
        $this->mockRepo('SystemParameter', Repository\SystemParameter::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $this->repoMap['SystemParameter']
            ->shouldReceive('getDigitalContinuationReminderPeriod')->with()->once()->andReturn(34);

        $this->repoMap['ContinuationDetail']
            ->shouldReceive('fetchListForDigitalReminders')->with(34)->once()->andReturn(
                [
                    (new ContinuationDetail())->setId(45),
                ]
            );

        $this->expectedSideEffect(
            Create::class,
            [
                'entityId' => 45,
                'type' => Queue::TYPE_CONT_DIGITAL_REMINDER,
                'status' => Queue::STATUS_QUEUED
            ],
            new Result()
        );

        $command = Command::create([]);
        $result = $this->sut->handleCommand($command);
        $this->assertEquals(['1 reminder queue jobs created'], $result->getMessages());
    }
}
