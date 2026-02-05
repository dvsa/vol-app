<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Organisation;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\Organisation\Dashboard;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Transfer\Query\Organisation\Dashboard as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\QueryHandler\Organisation\Dashboard::class)]
class DashboardTest extends QueryHandlerTestCase
{
    /** @var  Dashboard | m\MockInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Dashboard();

        $this->mockRepo('Organisation', OrganisationRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $organisationId = 69;
        $licenceId = 7;
        $applicationId = 1;
        $variationId = 2;

        $query = Qry::create(['id' => $organisationId]);

        $mockOrganisation = m::mock(OrganisationEntity::class)
            ->makePartial()
            ->setId($organisationId);
        $mockOrganisation
            ->shouldReceive('serialize')->once()->andReturn(['id' => $organisationId])
            ->getMock();

        $mockLicence = m::mock(LicenceEntity::class)
            ->makePartial()
            ->setId($licenceId);
        $mockLicence
            ->shouldReceive('serialize')->once()->andReturn(['id' => $licenceId]);

        $mockApplication = m::mock(ApplicationEntity::class)
            ->makePartial()
            ->setId($applicationId);
        $mockApplication
            ->shouldReceive('serialize')->once()->andReturn(['id' => $applicationId]);

        $mockVariation = m::mock(ApplicationEntity::class)
            ->makePartial()
            ->setId($variationId)
            ->setIsVariation(true);
        $mockVariation
            ->shouldReceive('getStatus->getId')
            ->andReturn(ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED);
        $mockVariation
            ->shouldReceive('serialize')->andReturn(['id' => $variationId]);

        $licences = new ArrayCollection();
        $licences->add($mockLicence);

        $mockOrganisation
            ->shouldReceive('getActiveLicences')
            ->once()
            ->andReturn($licences);

        $this->repoMap['Organisation']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockOrganisation);

        $this->repoMap['Application']
            ->shouldReceive('fetchByOrgAndStatusForActiveLicences')
            ->with(
                $organisationId,
                [
                    ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION,
                    ApplicationEntity::APPLICATION_STATUS_GRANTED,
                    ApplicationEntity::APPLICATION_STATUS_NOT_SUBMITTED
                ]
            )
            ->once()
            ->andReturn([$mockApplication, $mockVariation]);

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $expected = [
            'id' => 69,
            'dashboard' => [
                'licences' => [
                    ['id' => 7],
                ],
                'applications' => [
                    ['id' => 1],
                ],
                'variations' => [
                    [ 'id' => 2],
                ],
            ],
        ];

        $this->assertEquals($expected, $result->serialize());
    }
}
