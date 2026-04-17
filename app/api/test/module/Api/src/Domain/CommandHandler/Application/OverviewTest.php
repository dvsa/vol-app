<?php

declare(strict_types=1);

/**
 * Overview Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\Overview;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationTracking as ApplicationTrackingEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Transfer\Command\Application\Overview as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Overview Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OverviewTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Overview();
        $this->mockRepo('Application', ApplicationRepo::class);

        $this->references = [
            ApplicationEntity::class => [
                69 => m::mock(ApplicationEntity::class),
            ],
            ApplicationTrackingEntity::class => [
                99 => m::mock(ApplicationTrackingEntity::class)
            ],
            LicenceEntity::class => [
                7 => m::mock(LicenceEntity::class)
            ],
            OrganisationEntity::class => [
                1 => m::mock(OrganisationEntity::class)
            ],
            TrafficAreaEntity::class => [
                'B' => m::mock(TrafficAreaEntity::class)
            ],
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $applicationId  = 69;
        $licenceId      = 7;
        $organisationId = 1;
        $version        = 10;
        $trackingId     = 99;

        $command = Cmd::create(
            [
                'id' => $applicationId,
                'version' => $version,
                'leadTcArea' => 'B',
                'receivedDate' => '2015-06-10',
                'targetCompletionDate' => '2016-01-02',
                'tracking' => [
                    'id' => $trackingId,
                    'version' => 1,
                    'addressesStatus' => 2,
                ]
            ]
        );

        /** @var ApplicationEntity $application */
        $application = $this->mapReference(ApplicationEntity::class, $applicationId);

        /** @var ApplicationTrackingEntity $tracking */
        $tracking = $this->mapReference(ApplicationTrackingEntity::class, $trackingId);

        /** @var LicenceEntity $licence */
        $licence = $this->mapReference(LicenceEntity::class, $licenceId);

        /** @var OrganisationEntity $organisation */
        $organisation = $this->mapReference(OrganisationEntity::class, $organisationId);

        $licence->setOrganisation($organisation);
        $application->setLicence($licence);
        $application->setApplicationTracking($tracking);

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $version)
            ->andReturn($application);

        $this->repoMap['Application']
            ->shouldReceive('save')
            ->with($application)
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'application' => $applicationId,
                'applicationTracking' => $trackingId,
            ],
            'messages' => [
                'Application updated',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('2015-06-10', $application->getReceivedDate()->format('Y-m-d'));
        $this->assertEquals('2016-01-02', $application->getTargetCompletionDate()->format('Y-m-d'));
        $this->assertEquals(2, $application->getApplicationTracking()->getAddressesStatus());
        $this->assertEquals(
            $this->mapReference(TrafficAreaEntity::class, 'B'),
            $application->getLicence()->getOrganisation()->getLeadTcArea()
        );
    }

    public function testHandleCommandNoExistingApplicationTracking(): void
    {
        $applicationId  = 69;
        $licenceId      = 7;
        $organisationId = 1;
        $version        = 10;
        $trackingId     = 99;

        $command = Cmd::create(
            [
                'id' => $applicationId,
                'version' => $version,
                'leadTcArea' => 'B',
                'receivedDate' => '2015-06-10',
                'targetCompletionDate' => '2016-01-02',
                'tracking' => [
                    'id' => $trackingId,
                    'version' => 1,
                    'addressesStatus' => 2,
                ]
            ]
        );

        /** @var ApplicationEntity $application */
        $application = $this->mapReference(ApplicationEntity::class, $applicationId);

        /** @var LicenceEntity $licence */
        $licence = $this->mapReference(LicenceEntity::class, $licenceId);

        /** @var OrganisationEntity $organisation */
        $organisation = $this->mapReference(OrganisationEntity::class, $organisationId);

        $licence->setOrganisation($organisation);
        $application->setLicence($licence);

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $version)
            ->andReturn($application);

        $this->repoMap['Application']
            ->shouldReceive('save')
            ->with($application)
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'application' => $applicationId,
                'applicationTracking' => $trackingId,
            ],
            'messages' => [
                'Application updated',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('2015-06-10', $application->getReceivedDate()->format('Y-m-d'));
        $this->assertEquals('2016-01-02', $application->getTargetCompletionDate()->format('Y-m-d'));
        $this->assertEquals(2, $application->getApplicationTracking()->getAddressesStatus());
        $this->assertEquals(
            $this->mapReference(TrafficAreaEntity::class, 'B'),
            $application->getLicence()->getOrganisation()->getLeadTcArea()
        );
    }
}
