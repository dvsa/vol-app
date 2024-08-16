<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\GenerateReport as GenerateReportCmd;
use Dvsa\Olcs\Api\Domain\Command\Permits\RoadworthinessReport;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Permits\GenerateReport as GenerateReportHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * @see GenerateReportHandler
 */
class GenerateReportTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GenerateReportHandler();
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
        $user = 291;

        $cmdData = [
            'id' => $reportId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'user' => $user,
        ];

        $reportCmdData = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'user' => $user,
        ];

        $command = GenerateReportCmd::create($cmdData);

        $reportResultMessage = 'report result';
        $reportResult = new Result();
        $reportResult->addMessage($reportResultMessage);

        $this->expectedSideEffect(
            RoadworthinessReport::class,
            $reportCmdData,
            $reportResult
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                $reportResultMessage,
                sprintf(GenerateReportHandler::SUCCESS_MSG, $reportId),
            ],
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Test exception is thrown when the requested report has no config
     */
    public function testHandleCommandMissingReport(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(GenerateReportHandler::MISSING_REPORT_EXCEPTION);

        $cmdData = ['id' => 'missing'];
        $command = GenerateReportCmd::create($cmdData);
        $this->sut->handleCommand($command);
    }
}
