<?php

declare(strict_types=1);

/**
 * Reset Application Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOperatingCentre;
use Dvsa\Olcs\Transfer\Command\Application\CreateApplication;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\ResetApplication;
use Dvsa\Olcs\Api\Domain\Command\Application\ResetApplication as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Exception\RequiresConfirmationException;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;

/**
 * Reset Application Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ResetApplicationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ResetApplication();
        $this->mockRepo('Application', Application::class);
        $this->mockRepo('ApplicationOperatingCentre', ApplicationOperatingCentre::class);
        $this->mockRepo('Licence', Licence::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            LicenceEntity::LICENCE_CATEGORY_PSV,
            LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
            ApplicationEntity::APPLIED_VIA_POST,
            RefData::APP_VEHICLE_TYPE_PSV,
        ];

        parent::initReferences();
    }

    public function testHandleCommandRequireConfirmationInvalidValue(): void
    {
        $data = [
            'niFlag' => 'Y',
            'operatorType' => LicenceEntity::LICENCE_CATEGORY_PSV,
            'licenceType' => LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED
        ];
        $command = Cmd::create($data);

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setOrganisation($organisation);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandRequireConfirmation(): void
    {
        $data = [
            'niFlag' => 'N',
            'operatorType' => LicenceEntity::LICENCE_CATEGORY_PSV,
            'licenceType' => LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED,
            'vehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
        ];
        $command = Cmd::create($data);

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setOrganisation($organisation);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->expectException(RequiresConfirmationException::class);

        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('providerWithConfirm')]
    public function testHandleCommandRequireConfirmationWithConfirm(mixed $receivedDate, mixed $expectedCreateApp, mixed $associatedOperatingCentres): void
    {
        $data = [
            'niFlag' => 'N',
            'operatorType' => LicenceEntity::LICENCE_CATEGORY_PSV,
            'licenceType' => LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED,
            'confirm' => true
        ];
        $command = Cmd::create($data);

        $tasks = [
            m::mock(TaskEntity::class)->makePartial()->shouldReceive('getIsClosed')
                ->andReturn('N')->shouldReceive('setIsClosed')->with('Y')->getMock(),
            m::mock(TaskEntity::class)->makePartial()->shouldReceive('getIsClosed')
                ->andReturn('Y')->getMock()
        ];

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(222);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setOrganisation($organisation);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);
        $application->setTasks($tasks);
        $application->setReceivedDate($receivedDate);
        $application->setAppliedVia($this->mapRefData(ApplicationEntity::APPLIED_VIA_POST));

        if (!empty($associatedOperatingCentres)) {
            $this->repoMap['ApplicationOperatingCentre']->shouldReceive('delete')
                ->times(count($associatedOperatingCentres));
            $application->setOperatingCentres($associatedOperatingCentres);
        }

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->with($application)
            ->shouldReceive('delete')
            ->with($application);

        $this->repoMap['Licence']->shouldReceive('delete')
            ->with($licence);

        $result1 = new Result();
        $result1->addId('application', 123);
        $createAppData = $expectedCreateApp;
        $this->expectedSideEffect(CreateApplication::class, $createAppData, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'application' => 123
            ],
            'messages' => [
                '1 task(s) closed',
                'Licence removed',
                count($associatedOperatingCentres) . ' application operating centres associations removed',
                'Application removed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function providerWithConfirm(): array
    {
        return [
            [
                null,
                [
                    'organisation' => 222,
                    'niFlag' => 'N',
                    'operatorType' => LicenceEntity::LICENCE_CATEGORY_PSV,
                    'licenceType' => LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED,
                    'receivedDate' => null
                ],
                [
                    m::mock(\Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre::class),
                    m::mock(\Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre::class),
                    m::mock(\Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre::class),
                ]
            ],
            [
                new \DateTime('2015-01-01'),
                [
                    'organisation' => 222,
                    'niFlag' => 'N',
                    'operatorType' => LicenceEntity::LICENCE_CATEGORY_PSV,
                    'licenceType' => LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED,
                    'receivedDate' => '2015-01-01'
                ],
                [
                    // No Operating Centres
                ]
            ]
        ];
    }
}
