<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits\Report;

use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\Report\ReportList;
use Dvsa\Olcs\Api\Domain\Service\PermitsReportService;
use Dvsa\Olcs\Transfer\Query\Permits\ReportList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

class ReportListTest extends QueryHandlerTestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleQueryIsCallable(): void
    {
        $this->assertIsCallable($this->sut->handleQuery(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleQueryIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleQueryReturnsArrayFormat(): void
    {
        $result = $this->sut->handleQuery(Qry::create([]));

        $this->assertIsArray($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertArrayHasKey('count', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleQueryReturnsArrayFormat')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleQueryReturnsListOfAvailableReportsFromPermitReportService(): void
    {
        $result = $this->sut->handleQuery(Qry::create([]));
        $this->assertEquals(PermitsReportService::REPORT_TYPES, $result['result']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleQueryReturnsListOfAvailableReportsFromPermitReportService')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleQueryReturnsValidCountOfAvailableReportsFromPermitReportService(): void
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
