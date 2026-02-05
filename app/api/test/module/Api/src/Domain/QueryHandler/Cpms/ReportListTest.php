<?php

declare(strict_types=1);

/**
 * Cpms Report List Query Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cpms;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Cpms\ReportList;
use Dvsa\Olcs\Api\Service\CpmsHelperInterface as CpmsHelper;
use Dvsa\Olcs\Transfer\Query\Cpms\ReportList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Cpms Report List Query Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ReportListTest extends QueryHandlerTestCase
{
    protected $mockCpmsService;

    public function setUp(): void
    {
        $this->mockCpmsService = m::mock(CpmsHelper::class);

        $this->mockedSmServices = [
            'CpmsHelperService' => $this->mockCpmsService,
        ];

        $this->sut = new ReportList();

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);
        $data = [
            'items' => [
                [
                    "code" => "12AB34CD",
                    "name" => "Foo",
                    "description" => "Foo report"
                ],
                [
                    "code" => "12AB34CE",
                    "name" => "Bar",
                    "description" => "Bar report"
                ],
            ],
        ];

        $this->mockCpmsService
            ->shouldReceive('getReportList')
            ->once()
            ->andReturn($data);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => [
                [
                    "code" => "12AB34CD",
                    "name" => "Foo",
                    "description" => "Foo report"
                ],
                [
                    "code" => "12AB34CE",
                    "name" => "Bar",
                    "description" => "Bar report"
                ],
            ],
            'count' => 2,
        ];

        $this->assertEquals($expected, $result);
    }
}
