<?php

declare(strict_types=1);

/**
 * Cpms Report Status Query Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cpms;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Cpms\ReportStatus;
use Dvsa\Olcs\Api\Service\CpmsHelperInterface as CpmsHelper;
use Dvsa\Olcs\Transfer\Query\Cpms\ReportStatus as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Cpms Report Status Query Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ReportStatusTest extends QueryHandlerTestCase
{
    protected $mockCpmsService;

    public function setUp(): void
    {
        $this->mockCpmsService = m::mock(CpmsHelper::class);

        $this->mockedSmServices = [
            'CpmsHelperService' => $this->mockCpmsService,
        ];

        $this->sut = new ReportStatus();

        parent::setUp();
    }

    public function testHandleQuerySuccess(): void
    {
        $reference = 'OLCS-1234-ABCD';
        $query = Qry::create(['reference' => $reference]);
        $data = [
            "completed" => true,
            "total_rows" => 0,
            "processed_rows" => 0,
            "report_type" => "Daily Balance",
            "report_type_code" => "ED7AAFBC",
            "report_filters" => [
                "scope" => [
                    "CASH",
                    "CHEQUE",
                    "POSTAL_ORDER"
                ],
                "from" => "2015-09-30 16:50:40.000000",
                "to" => "2015-10-01 16:50:40.000000"
            ],
            "download_url" => "http://payment-service.testdomain/api/report/OLCS-1234-ABCD/download?token=secrettoken",
            "download_size" => 108,
            "file_extension" => "csv",
            "error" => false
        ];

        $this->mockCpmsService
            ->shouldReceive('getReportStatus')
            ->once()
            ->with($reference)
            ->andReturn($data);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'completed' => true,
            'token' => 'secrettoken',
            'extension' => 'csv',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testHandleQueryNotFound(): void
    {
        $reference = 'OLCS-1234-INVALID';
        $query = Qry::create(['reference' => $reference]);
        $data = [];

        $this->mockCpmsService
            ->shouldReceive('getReportStatus')
            ->once()
            ->with($reference)
            ->andReturn($data);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);

        $this->sut->handleQuery($query);
    }

    public function testHandleQueryNotReady(): void
    {
        $reference = 'OLCS-1234-INVALID';
        $query = Qry::create(['reference' => $reference]);
        $data = [
            'completed' => false,
        ];

        $this->mockCpmsService
            ->shouldReceive('getReportStatus')
            ->once()
            ->with($reference)
            ->andReturn($data);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\NotReadyException::class);

        $this->sut->handleQuery($query);
    }
}
