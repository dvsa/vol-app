<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Permits\QueueReport as QueueReportHandler;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Transfer\Command\Permits\QueueReport as QueueReportCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * @see QueueReportHandler
 */
class QueueReportTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueueReportHandler();
        parent::setUp();
    }

    /**
     * test the handle command, using the roadworthiness report as an example
     */
    public function testHandleCommand(): void
    {
        $reportId = 'cert_roadworthiness';
        $startDate = '2020-12-25';
        $endDate = '2020-12-31';

        $cmdData = [
            'id' => $reportId,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        $command = QueueReportCmd::create($cmdData);

        $queueResultMessage = 'queue result';
        $queueResult = new Result();
        $queueResult->addMessage($queueResultMessage);

        $this->expectedQueueSideEffect(null, Queue::TYPE_PERMIT_REPORT, $cmdData, $queueResult);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                $queueResultMessage,
                sprintf(QueueReportHandler::SUCCESS_MSG, $reportId),
            ],
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Test exception is thrown when the requested report has no config
     */
    public function testHandleCommandMissingReport(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(QueueReportHandler::MISSING_REPORT_EXCEPTION);

        $cmdData = ['id' => 'missing'];
        $command = QueueReportCmd::create($cmdData);
        $this->sut->handleCommand($command);
    }
}
