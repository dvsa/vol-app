<?php

declare(strict_types=1);

/**
 * Cpms Request Report Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cpms;

use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueueCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cpms\RequestReport;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Service\CpmsHelperInterface as CpmsHelper;
use Dvsa\Olcs\Transfer\Command\Cpms\RequestReport as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 *  Cpms Request Report Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class RequestReportTest extends AbstractCommandHandlerTestCase
{
    public $mockCpmsService;
    protected $mockApi;

    public function setUp(): void
    {
        $this->mockCpmsService = m::mock(CpmsHelper::class);

        $this->mockedSmServices = [
            'CpmsHelperService' => $this->mockCpmsService
        ];

        $this->sut = new RequestReport();

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $reportCode = 'FOO';
        $start = '2015-10-07 00:00:00';
        $endDate = new DateTime('now');
        $end = $endDate->format('Y-m-d H:i:s');

        $filename = 'FILENAME';

        // expectations
        $this->mockCpmsService
            ->shouldReceive('requestReport')
            ->once()
            ->with($reportCode, m::type(\DateTime::class), m::type(\DateTime::class))
            ->andReturnUsing(
                function ($code, $startDatetime, $endDatetime) use ($start, $end) {
                    $this->assertEquals($start, $startDatetime->format('Y-m-d H:i:s'));
                    $this->assertEquals($end, $endDatetime->format('Y-m-d H:i:s'));
                    return [
                        'reference' => 'OLCS-1234-FOO',
                        'code' => \Dvsa\Olcs\Api\Service\CpmsHelperInterface::RESPONSE_SUCCESS,
                    ];
                }
            );

        $queueResult = new Result();
        $queueResult
            ->addId('queue', 99)
            ->addMessage('Queue created');
        $this->expectedSideEffect(
            CreateQueueCmd::class,
            [
                'type' => Queue::TYPE_CPMS_REPORT_DOWNLOAD,
                'status' => Queue::STATUS_QUEUED,
                'options' => '{"reference":"OLCS-1234-FOO","name":"FILENAME"}',
            ],
            $queueResult
        );

        // invoke
        $command = Cmd::create(
            [
                'reportCode' => $reportCode,
                'start' => $start,
                'end' => $end,
                'name' => $filename,
            ]
        );
        $result = $this->sut->handleCommand($command);

        // assertions
        $this->assertEquals(['Queue created', 'Report requested'], $result->getMessages());
        $this->assertEquals(['queue' => 99, 'cpmsReport' => 'OLCS-1234-FOO'], $result->getIds());
    }

    public function testHandleCommandErrorFromCpms(): void
    {
        $reportCode = 'FOO';
        $start = '2015-10-07 00:00:00';
        $end = '2015-10-08 23:59:59';
        $filename = 'FILENAME';

        // expectations
        $this->mockCpmsService
            ->shouldReceive('requestReport')
            ->once()
            ->with($reportCode, m::type(\DateTime::class), m::type(\DateTime::class))
            ->andReturnUsing(
                function ($code, $startDatetime, $endDatetime) use ($start, $end) {
                    $this->assertEquals($start, $startDatetime->format('Y-m-d H:i:s'));
                    $this->assertEquals($end, $endDatetime->format('Y-m-d H:i:s'));
                    return [
                        'message' => 'MESSAGE',
                        'code' => 'XXX',

                    ];
                }
            );

        // invoke
        $command = Cmd::create(
            [
                'reportCode' => $reportCode,
                'start' => $start,
                'end' => $end,
                'name' => $filename,
            ]
        );

        try {
            $this->sut->handleCommand($command);
            $this->fail('Exception should have been thrown');
        } catch (\Dvsa\Olcs\Api\Domain\Exception\BadRequestException $e) {
            $this->assertSame(['MESSAGE'], $e->getMessages());
        }
    }
}
