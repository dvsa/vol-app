<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Letter\PreviewRecord;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\Letter\PreviewRecord\Lookup as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\Letter\PreviewRecord\Lookup as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Lookup PreviewRecord QueryHandler Test
 */
final class LookupTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    private function licence(): m\MockInterface
    {
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->shouldReceive('getName')->andReturn('John Smith Haulage Ltd.');

        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->shouldReceive('getDescription')->andReturn('Goods');

        $newStatus = m::mock(RefData::class)->makePartial();
        $newStatus->shouldReceive('getDescription')->andReturn('Not submitted');

        $older = m::mock(ApplicationEntity::class)->makePartial();
        $older->shouldReceive('getId')->andReturn(1);
        $older->shouldReceive('getStatus')->andReturn($newStatus);
        $older->shouldReceive('getIsVariation')->andReturn(false);

        $newer = m::mock(ApplicationEntity::class)->makePartial();
        $newer->shouldReceive('getId')->andReturn(2);
        $newer->shouldReceive('getStatus')->andReturn($newStatus);
        $newer->shouldReceive('getIsVariation')->andReturn(true);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('getId')->andReturn(7);
        $licence->shouldReceive('getLicNo')->andReturn('OB1234567');
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);
        $licence->shouldReceive('getGoodsOrPsv')->andReturn($goodsOrPsv);
        $licence->shouldReceive('isNi')->andReturn(false);
        $licence->shouldReceive('getApplications')->andReturn(new ArrayCollection([$older, $newer]));

        return $licence;
    }

    public function testALicenceNumberResolvesByLicNo(): void
    {
        $this->repoMap['Licence']->shouldReceive('fetchByLicNoWithoutAdditionalData')
            ->with('OB1234567')->once()->andReturn($this->licence());
        $this->repoMap['Licence']->shouldNotReceive('fetchById');

        $result = $this->sut->handleQuery(Qry::create(['term' => 'OB1234567']));

        $this->assertTrue($result['found']);
        $this->assertSame('OB1234567', $result['licence']['licNo']);
        $this->assertSame('John Smith Haulage Ltd.', $result['licence']['organisationName']);
    }

    public function testABareNumberResolvesByDatabaseId(): void
    {
        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(7)->once()->andReturn($this->licence());
        $this->repoMap['Licence']->shouldNotReceive('fetchByLicNoWithoutAdditionalData');

        $result = $this->sut->handleQuery(Qry::create(['term' => '7']));

        $this->assertTrue($result['found']);
        $this->assertSame(7, $result['licence']['id']);
    }

    public function testApplicationsComeNewestFirstWithStatusAndVariationFlag(): void
    {
        $this->repoMap['Licence']->shouldReceive('fetchByLicNoWithoutAdditionalData')
            ->andReturn($this->licence());

        $result = $this->sut->handleQuery(Qry::create(['term' => 'OB1234567']));

        $this->assertSame([2, 1], array_column($result['applications'], 'id'));
        $this->assertTrue($result['applications'][0]['isVariation']);
        $this->assertSame('Not submitted', $result['applications'][0]['status']);
    }

    public function testAMissIsFoundFalseNotAnError(): void
    {
        // This backs a typeahead: a half-typed licence number is the normal case, not a 404
        $this->repoMap['Licence']->shouldReceive('fetchByLicNoWithoutAdditionalData')
            ->andThrow(new NotFoundException());

        $result = $this->sut->handleQuery(Qry::create(['term' => 'OB99']));

        $this->assertFalse($result['found']);
        $this->assertArrayNotHasKey('licence', $result);
    }

    public function testApplicationsAreCappedAtTheNewestTwentyFive(): void
    {
        $status = m::mock(RefData::class)->makePartial();
        $status->shouldReceive('getDescription')->andReturn('Valid');

        $applications = [];
        for ($i = 1; $i <= 30; $i++) {
            $app = m::mock(ApplicationEntity::class)->makePartial();
            $app->shouldReceive('getId')->andReturn($i);
            $app->shouldReceive('getStatus')->andReturn($status);
            $app->shouldReceive('getIsVariation')->andReturn(false);
            $applications[] = $app;
        }

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('getId')->andReturn(7);
        $licence->shouldReceive('getLicNo')->andReturn('OB1234567');
        $licence->shouldReceive('getOrganisation')->andReturn(null);
        $licence->shouldReceive('getGoodsOrPsv')->andReturn(null);
        $licence->shouldReceive('isNi')->andReturn(false);
        $licence->shouldReceive('getApplications')->andReturn(new ArrayCollection($applications));

        $this->repoMap['Licence']->shouldReceive('fetchByLicNoWithoutAdditionalData')->andReturn($licence);

        $result = $this->sut->handleQuery(Qry::create(['term' => 'OB1234567']));

        $this->assertCount(25, $result['applications']);
        $this->assertTrue($result['applicationsTruncated']);
        $this->assertSame(30, $result['applications'][0]['id'], 'newest survive the cap');
        $this->assertSame(6, $result['applications'][24]['id']);
    }
}
