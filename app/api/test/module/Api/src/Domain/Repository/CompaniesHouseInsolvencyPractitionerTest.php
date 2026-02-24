<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseInsolvencyPractitioner as InsolvencyPractitionerRepository;

class CompaniesHouseInsolvencyPractitionerTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(InsolvencyPractitionerRepository::class);
    }

    public function testFetchByCompany(): void
    {
        $companyNumber = '01234567';

        $qb = $this->createMockQb('{QUERY}');

        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn(['Result']);

        $this->mockCreateQueryBuilder($qb);

        $this->sut->fetchByCompany($companyNumber);

        $expectedQuery = '{QUERY} AND m.companiesHouseCompany = [[' . $companyNumber . ']]';

        self::assertEquals($expectedQuery, $this->query);
    }
}
