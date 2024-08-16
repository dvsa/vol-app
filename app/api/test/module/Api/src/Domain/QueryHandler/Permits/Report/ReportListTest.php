<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits\Report;

use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\Report\ReportList;
use Dvsa\Olcs\Api\Domain\Service\PermitsReportService;
use Dvsa\Olcs\Transfer\Query\Permits\ReportList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

class ReportListTest extends QueryHandlerTestCase
{
    /**
     * @test
     */
    public function handleQueryIsCallable()
    {
        $this->assertIsCallable($this->sut->handleQuery(...));
    }

    /**
     * @test
     * @depends handleQueryIsCallable
     */
    public function handleQueryReturnsArrayFormat()
    {
        $result = $this->sut->handleQuery(Qry::create([]));

        $this->assertIsArray($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertArrayHasKey('count', $result);
    }

    /**
     * @test
     * @depends handleQueryReturnsArrayFormat
     */
    public function handleQueryReturnsListOfAvailableReportsFromPermitReportService()
    {
        $result = $this->sut->handleQuery(Qry::create([]));
        $this->assertEquals(PermitsReportService::REPORT_TYPES, $result['result']);
    }

    /**
     * @test
     * @depends handleQueryReturnsListOfAvailableReportsFromPermitReportService
     */
    public function handleQueryReturnsValidCountOfAvailableReportsFromPermitReportService()
    {
        $result = $this->sut->handleQuery(Qry::create([]));
        $this->assertEquals(count(PermitsReportService::REPORT_TYPES), $result['count']);
    }

    public function setUp(): void
    {
        $this->sut = new ReportList();
        parent::setUp();
    }
}
