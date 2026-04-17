<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\QueryHandler\CompaniesHouse;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Repository\Organisation;
use Dvsa\Olcs\Cli\Domain\QueryHandler\CompaniesHouse\Organisations;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Cli\Domain\Query\CompaniesHouse\Organisations as Qry;
use Mockery as m;

class OrganisationsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Organisations();
        $this->mockRepo('Organisation', Organisation::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $this->repoMap['Organisation']->shouldReceive('getAllOrganisationsForCompaniesHouse')
            ->andReturn([
                ['company_or_llp_no' => 123],
                ['company_or_llp_no' => 456],
            ])
            ->once()
            ->getMock();

        $result = $this->sut->handleQuery($query);

        $expected = [
            ['company_or_llp_no' => 123],
            ['company_or_llp_no' => 456],
        ];

        $this->assertEquals($result, $expected);
    }
}
