<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\CompaniesHouseCompanyBundle as Qry;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\CompaniesHouseCompanyBundle;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseCompany;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany as CompaniesHouseCompanyEntity;

class CompaniesHouseCompanyBundleTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CompaniesHouseCompanyBundle();
        $this->mockRepo('CompaniesHouseCompany', CompaniesHouseCompany::class);
        $this->mockRepo('Licence', Licence::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $licenceEntity = m::mock(LicenceEntity::class);
        $companiesHouseCompanyEntity = m::mock(CompaniesHouseCompanyEntity::class);

        $licenceEntity->shouldReceive('getOrganisation->getCompanyOrLlpNo')->andReturn(12345678);

        $this->repoMap['CompaniesHouseCompany']->shouldReceive('getLatestByCompanyNumber')->once()
            ->andReturn($companiesHouseCompanyEntity);

        $this->repoMap['Licence']->shouldReceive('fetchById')->with(111)
            ->andReturn($licenceEntity);

        $companiesHouseCompanyEntity->shouldReceive('serialize')->once()
            ->andReturn(['SERIALIZED']);

        $query = Qry::create(['id' => 111, 'bundle' => []]);

        $this->assertEquals(['SERIALIZED'], $this->sut->handleQuery($query));
    }
}
