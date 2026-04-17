<?php

declare(strict_types=1);

/**
 * Inspection Request / Create
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\InspectionRequest;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\InspectionRequest\Create;
use Dvsa\Olcs\Api\Domain\Repository\InspectionRequest as InspectionRequestRepo;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Create as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Inspection\InspectionRequest as InspectionRequestEntity;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre as OperatingCentreEntity;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Domain\Command\InspectionRequest\SendInspectionRequest as SendInspectionRequestCmd;

/**
 * Inspection Request / Create
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Create();
        $this->mockRepo('InspectionRequest', InspectionRequestRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            InspectionRequestEntity::REQUEST_TYPE_NEW_OP,
            InspectionRequestEntity::RESULT_TYPE_NEW,
            InspectionRequestEntity::REPORT_TYPE_MAINTENANCE_REQUEST,
        ];

        $this->references = [
            LicenceEntity::class => [
                1 => m::mock(LicenceEntity::class)
            ],
            ApplicationEntity::class => [
                2 => m::mock(ApplicationEntity::class)
            ],
            OperatingCentreEntity::class => [
                3 => m::mock(OperatingCentreEntity::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandCreateFromApplication(): void
    {
        $this->mockAuthService();

        $licenceId = 1;
        $applicationId = 2;
        $ocId = 3;

        $data = [
            'type' => 'application',
            'application' => $applicationId,
            'licence' => $licenceId,
            'requestType' => InspectionRequestEntity::REQUEST_TYPE_NEW_OP,
            'requestDate' => '2015-01-01',
            'dueDate' => '2016-01-01',
            'resultType' => InspectionRequestEntity::RESULT_TYPE_NEW,
            'requestorNotes' => 'reqnotes',
            'reportType' => InspectionRequestEntity::REPORT_TYPE_MAINTENANCE_REQUEST,
            'operatingCentre' => $ocId,
            'inspectorName' => 'iname',
            'returnDate' => '2016-01-01',
            'fromDate' => '2014-01-01',
            'toDate' => '2015-01-01',
            'vehiclesExaminedNo' => 1,
            'trailersExaminedNo' => 2,
            'inspectorNotes' => 'inspnotes'
        ];

        $command = Cmd::create($data);

        $this->repoMap['Application']
            ->shouldReceive('fetchWithLicence')
            ->with($applicationId)
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn($applicationId)
                ->once()
                ->shouldReceive('getLicence')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getId')
                    ->andReturn($licenceId)
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $inspectionRequest = null;

        $this->repoMap['InspectionRequest']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(InspectionRequestEntity::class))
            ->andReturnUsing(
                function (InspectionRequestEntity $ir) use (&$inspectionRequest) {
                    $ir->setId(111);
                    $inspectionRequest = $ir;
                }
            );

        $inspectionRerquestSendEmailResult = new Result();
        $inspectionRerquestSendEmailResult->addMessage('Inspection request email sent');
        $this->expectedSideEffect(
            SendInspectionRequestCmd::class,
            [
                'id' => 111
            ],
            $inspectionRerquestSendEmailResult
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'inspectionRequest' => 111
            ],
            'messages' => [
                'Inspection request created successfully',
                'Inspection request email sent'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandCreateFromLicence(): void
    {
        $this->mockAuthService();

        $licenceId = 1;
        $ocId = 3;

        $data = [
            'type' => 'licence',
            'licence' => $licenceId,
            'requestType' => InspectionRequestEntity::REQUEST_TYPE_NEW_OP,
            'requestDate' => '2015-01-01',
            'dueDate' => '2016-01-01',
            'resultType' => InspectionRequestEntity::RESULT_TYPE_NEW,
            'requestorNotes' => 'reqnotes',
            'reportType' => InspectionRequestEntity::REPORT_TYPE_MAINTENANCE_REQUEST,
            'operatingCentre' => $ocId,
            'inspectorName' => 'iname',
            'returnDate' => '2016-01-01',
            'fromDate' => '2014-01-01',
            'toDate' => '2015-01-01',
            'vehiclesExaminedNo' => 1,
            'trailersExaminedNo' => 2,
            'inspectorNotes' => 'inspnotes'
        ];

        $command = Cmd::create($data);

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn($licenceId)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $inspectionRequest = null;

        $this->repoMap['InspectionRequest']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(InspectionRequestEntity::class))
            ->andReturnUsing(
                function (InspectionRequestEntity $ir) use (&$inspectionRequest) {
                    $ir->setId(111);
                    $inspectionRequest = $ir;
                }
            );

        $inspectionRerquestSendEmailResult = new Result();
        $inspectionRerquestSendEmailResult->addMessage('Inspection request email sent');
        $this->expectedSideEffect(
            SendInspectionRequestCmd::class,
            [
                'id' => 111
            ],
            $inspectionRerquestSendEmailResult
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'inspectionRequest' => 111
            ],
            'messages' => [
                'Inspection request created successfully',
                'Inspection request email sent'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    protected function mockAuthService(): void
    {
        /** @var Team $mockTeam */
        $mockTeam = m::mock(Team::class)->makePartial();
        $mockTeam->setId(2);

        /** @var User $mockUser */
        $mockUser = m::mock(User::class)->makePartial();
        $mockUser->setId(1);
        $mockUser->setTeam($mockTeam);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);
    }
}
