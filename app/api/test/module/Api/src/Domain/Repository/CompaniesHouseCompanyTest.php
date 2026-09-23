<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseCompany as CompaniesHouseCompanyRepo;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany as Entity;
use Mockery as m;

final class CompaniesHouseCompanyTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(CompaniesHouseCompanyRepo::class);
    }

    public function testGetLatestByCompanyNumber(): void
    {
        $companyNumber = '01234567';
        $result = m::mock(Entity::class);

        $qb = $this->createRealQb()->willReturn([$result]);

        $this->assertSame($result, $this->sut->getLatestByCompanyNumber($companyNumber));

        $this->assertSame(
            'SELECT cc FROM ' . Entity::class . ' cc'
            . ' WHERE cc.companyNumber = :companyNumber'
            . ' ORDER BY cc.createdOn DESC',
            $qb->getDQL(),
        );
        $this->assertSame($companyNumber, $qb->getParameter('companyNumber')->getValue());
        $this->assertSame(1, $qb->getMaxResults());
    }

    public function testGetLatesByCompanyNumberNotFound(): void
    {
        $this->createRealQb()->willReturn([]);

        $this->expectException(NotFoundException::class);

        $this->sut->getLatestByCompanyNumber('01234567');
    }
}
