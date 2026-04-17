<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\QueryHandler\CompaniesHouse\InsolvencyPractitioner;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseInsolvencyPractitioner;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseInsolvencyPractitioner as InsolvencyPractitionerEntity;
use Dvsa\Olcs\Transfer\Query\CompaniesHouse\InsolvencyPractitioner as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class InsolvencyPractitionerTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new InsolvencyPractitioner();
        $this->mockRepo('CompaniesHouseInsolvencyPractitioner', CompaniesHouseInsolvencyPractitioner::class);
        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $data = [
            'id' => '12345678'
        ];
        $query = Query::create($data);

        $mockPractitioner = m::mock(InsolvencyPractitionerEntity::class);
        $this->repoMap['CompaniesHouseInsolvencyPractitioner']->shouldReceive('fetchbyCompany')->with('12345678')->andReturn([$mockPractitioner]);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => [
                $mockPractitioner
            ]
        ];

        $this->assertEquals($expected, $result);
    }
}
