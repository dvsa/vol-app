<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterInstance\GenerationContext as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRegRepo;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManager as TransportManagerRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Transfer\Query\Letter\LetterInstance\GenerationContext as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

final class GenerationContextTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('Cases', CasesRepo::class);
        $this->mockRepo('BusReg', BusRegRepo::class);
        $this->mockRepo('TransportManager', TransportManagerRepo::class);
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('Organisation', OrganisationRepo::class);

        parent::setUp();
    }

    private function refData(string $id): RefData
    {
        $refData = new RefData();
        $refData->setId($id);

        return $refData;
    }

    private function licence(?string $goodsOrPsv, bool $isNi): LicenceEntity
    {
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(111);
        $licence->shouldReceive('getOrganisation')->andReturnNull();
        $licence->shouldReceive('getGoodsOrPsv')->andReturn($goodsOrPsv === null ? null : $this->refData($goodsOrPsv));
        $licence->shouldReceive('isNi')->andReturn($isNi);

        return $licence;
    }

    public function testLicenceOnly(): void
    {
        $this->repoMap['Licence']->shouldReceive('fetchById')->with(111)->once()
            ->andReturn($this->licence(LicenceEntity::LICENCE_CATEGORY_PSV, true));

        $result = $this->sut->handleQuery(Qry::create(['licence' => 111]));

        $this->assertSame(['goodsOrPsv' => LicenceEntity::LICENCE_CATEGORY_PSV, 'isNi' => true], $result);
    }

    public function testApplicationGoodsOrPsvWinsOverItsLicence(): void
    {
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(222);
        $application->shouldReceive('getLicence')->andReturn($this->licence(LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, false));
        $application->shouldReceive('getGoodsOrPsv')->andReturn($this->refData(LicenceEntity::LICENCE_CATEGORY_PSV));

        $this->repoMap['Application']->shouldReceive('fetchById')->with(222)->once()->andReturn($application);

        $result = $this->sut->handleQuery(Qry::create(['application' => 222]));

        $this->assertSame(['goodsOrPsv' => LicenceEntity::LICENCE_CATEGORY_PSV, 'isNi' => false], $result);
    }

    public function testCaseUsesItsLicence(): void
    {
        $case = m::mock(CasesEntity::class)->makePartial();
        $case->setId(333);
        $case->shouldReceive('getLicence')->andReturn($this->licence(LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, true));
        $case->shouldReceive('getApplication')->andReturnNull();

        $this->repoMap['Cases']->shouldReceive('fetchById')->with(333)->once()->andReturn($case);

        $result = $this->sut->handleQuery(Qry::create(['case' => 333]));

        $this->assertSame(['goodsOrPsv' => LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, 'isNi' => true], $result);
    }

    public function testBusRegUsesItsLicence(): void
    {
        $busReg = m::mock(BusRegEntity::class)->makePartial();
        $busReg->setId(444);
        $busReg->shouldReceive('getLicence')->andReturn($this->licence(LicenceEntity::LICENCE_CATEGORY_PSV, false));

        $this->repoMap['BusReg']->shouldReceive('fetchById')->with(444)->once()->andReturn($busReg);

        $result = $this->sut->handleQuery(Qry::create(['busReg' => 444]));

        $this->assertSame(['goodsOrPsv' => LicenceEntity::LICENCE_CATEGORY_PSV, 'isNi' => false], $result);
    }

    public function testTransportManagerHasNoContext(): void
    {
        $transportManager = m::mock(TransportManagerEntity::class)->makePartial();
        $transportManager->setId(555);

        $this->repoMap['TransportManager']->shouldReceive('fetchById')->with(555)->once()
            ->andReturn($transportManager);

        $result = $this->sut->handleQuery(Qry::create(['transportManager' => 555]));

        $this->assertSame(['goodsOrPsv' => null, 'isNi' => null], $result);
    }

    public function testNothingGivenReturnsNulls(): void
    {
        $result = $this->sut->handleQuery(Qry::create([]));

        $this->assertSame(['goodsOrPsv' => null, 'isNi' => null], $result);
    }
}
