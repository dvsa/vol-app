<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseInsolvencyPractitioner as InsolvencyPractitionerRepository;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseInsolvencyPractitioner as Entity;

final class CompaniesHouseInsolvencyPractitionerTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(InsolvencyPractitionerRepository::class);
    }

    public function testFetchByCompany(): void
    {
        $companyNumber = '01234567';

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['Result']);

        $this->assertSame(['Result'], $this->sut->fetchByCompany($companyNumber));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.companiesHouseCompany = :companiesHouseCompany',
            $qb->getDQL(),
        );
        $this->assertSame($companyNumber, $qb->getParameter('companiesHouseCompany')->getValue());
    }
}
