<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Workshop;

use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\QueryHandler\Workshop\Workshop;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\Workshop as WorkshopEntity;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Workshop as WorkshopRepo;
use Dvsa\Olcs\Transfer\Query\Workshop\Workshop as Qry;
use Mockery as m;

class WorkshopTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Workshop();
        $this->mockRepo('Workshop', WorkshopRepo::class);

        parent::setUp();
    }

    public function testHandleQueryNoCheck(): void
    {
        $query = Qry::create(['id' => 111]);

        $workshop = $this->getWorkshopEntityMock();
        $this->workshopRepo($query, $workshop);

        $this->assertEquals(['foo'], $this->sut->handleQuery($query)->serialize());
    }

    public function testHandleQueryLicenceCheck(): void
    {
        $licenceId = 222;
        $query = Qry::create(['id' => 111, 'licence' => $licenceId]);

        $licence = m::mock(Licence::class);
        $licence->expects('getId')->withNoArgs()->andReturn($licenceId);

        $workshop = $this->getWorkshopEntityMockWithLicence($licence);

        $this->workshopRepo($query, $workshop);

        $this->assertEquals(['foo'], $this->sut->handleQuery($query)->serialize());
    }

    public function testHandleQueryLicenceMismatch(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(Workshop::ERR_LICENCE_MISMATCH);

        $query = Qry::create(['id' => 111, 'licence' => 222]);

        $licence = m::mock(Licence::class);
        $licence->expects('getId')->withNoArgs()->andReturn(333);

        $workshop = $this->getWorkshopEntityForExceptions($licence);

        $this->workshopRepo($query, $workshop);

        $this->assertEquals(['foo'], $this->sut->handleQuery($query)->serialize());
    }

    public function testHandleQueryApplicationCheck(): void
    {
        $appId = 222;
        $query = Qry::create(['id' => 111, 'application' => $appId]);

        $licence = m::mock(Licence::class);
        $licence->expects('isRelatedToApplication')->with($appId)->andReturnTrue();

        $workshop = $this->getWorkshopEntityMockWithLicence($licence);

        $this->workshopRepo($query, $workshop);

        $this->assertEquals(['foo'], $this->sut->handleQuery($query)->serialize());
    }

    public function testHandleQueryApplicationMismatch(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(Workshop::ERR_APP_MISMATCH);

        $appId = 222;
        $query = Qry::create(['id' => 111, 'application' => $appId]);

        $licence = m::mock(Licence::class);
        $licence->expects('isRelatedToApplication')->with($appId)->andReturnFalse();

        $workshop = $this->getWorkshopEntityForExceptions($licence);

        $this->workshopRepo($query, $workshop);

        $this->assertEquals(['foo'], $this->sut->handleQuery($query)->serialize());
    }

    private function getWorkshopEntityMock(): m\MockInterface
    {
        $workshop = m::mock(WorkshopEntity::class);
        $workshop->expects('serialize')->with(Workshop::BUNDLE)->andReturn(['foo']);

        return $workshop;
    }

    private function getWorkshopEntityMockWithLicence(m\MockInterface $licence): m\MockInterface
    {
        $workshop = $this->getWorkshopEntityMock();
        $workshop->expects('getLicence')->andReturn($licence);

        return $workshop;
    }

    private function getWorkshopEntityForExceptions(m\MockInterface $licence): m\MockInterface
    {
        $workshop = m::mock(WorkshopEntity::class);
        $workshop->expects('serialize')->never();
        $workshop->expects('getLicence')->andReturn($licence);

        return $workshop;
    }

    private function workshopRepo(Qry $query, m\MockInterface $workshop): void
    {
        $this->repoMap['Workshop']->expects('fetchUsingId')
            ->with($query)
            ->andReturn($workshop);
    }
}
