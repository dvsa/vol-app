<?php

declare(strict_types=1);

/**
 * Inspection Request / CreateFromGrant
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\InspectionRequest;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea;
use Dvsa\Olcs\Api\Entity\Inspection\InspectionRequest;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\InspectionRequest\CreateFromGrant;
use Dvsa\Olcs\Api\Domain\Repository\InspectionRequest as InspectionRequestRepo;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\CreateFromGrant as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Inspection\InspectionRequest as InspectionRequestEntity;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre as OperatingCentreEntity;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Domain\Command\InspectionRequest\SendInspectionRequest as SendInspectionRequestCmd;

/**
 * Inspection Request / CreateFromGrant
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateFromGrantTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateFromGrant();
        $this->mockRepo('InspectionRequest', InspectionRequestRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);

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
            InspectionRequestEntity::REPORT_TYPE_MAINTENANCE_REQUEST
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

    public function testHandleCommand(): void
    {
        $this->mockAuthService();

        $licenceId = 1;
        $applicationId = 2;
        $ocId = 3;

        $data = [
            'application' => $applicationId,
            'duePeriod' => 3,
            'caseworkerNotes' => 'cwnotes'
        ];

        $enforcementArea = 'EA-H';
        $command = $this->setUpMocks($data, $ocId, $licenceId, $enforcementArea, $applicationId);

        $this->expectedSideEffect(
            SendInspectionRequestCmd::class,
            [
                'id' => 111
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'inspectionRequest' => 111
            ],
            'messages' => [
                'Inspection request created successfully'
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

    public function testNIEmailsNotSent(): void
    {
        $licenceId = 1;
        $applicationId = 2;
        $ocId = 3;

        $data = [
            'application' => $applicationId,
            'duePeriod' => 3,
            'caseworkerNotes' => 'cwnotes'
        ];

        $enforcementArea = 'EA-N';
        $mockApplication = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getEnforcementArea')
                    ->andReturn(
                        m::mock(EnforcementArea::class)
                            ->shouldReceive('getId')
                            ->andReturn($enforcementArea)
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchWithLicenceAndOc')
            ->with($applicationId)
            ->andReturn($mockApplication)
            ->once()
            ->getMock();

        $command = Cmd::create($data);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [

            ],
            'messages' => [

            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * setUpMocks
     *
     * @param $data
     * @param $ocId
     * @param $licenceId
     * @param $enforcementArea
     * @param $applicationId
     *
     * @return Cmd
     */
    private function setUpMocks(mixed $data, mixed $ocId, mixed $licenceId, mixed $enforcementArea, mixed $applicationId): Cmd
    {
        $command = Cmd::create($data);

        $mockOperatingCentre = m::mock()
            ->shouldReceive('getId')
            ->andReturn($ocId)
            ->once()
            ->getMock();

        $operatingCentres = [$mockOperatingCentre];

        $mockApplication = m::mock()
            ->shouldReceive('getOcForInspectionRequest')
            ->andReturn($operatingCentres)
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn($licenceId)
                    ->once()
                    ->getMock()
                    ->shouldReceive('getEnforcementArea')
                    ->andReturn(
                        m::mock(EnforcementArea::class)
                            ->shouldReceive('getId')
                            ->andReturn($enforcementArea)
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->twice()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchWithLicenceAndOc')
            ->with($applicationId)
            ->andReturn($mockApplication)
            ->once()
            ->getMock();

        $inspectionRequest = new InspectionRequestEntity();
        $inspectionRequest->setRequestType($this->refData[0]);
        $inspectionRequest->setId(111);

        $this->repoMap['InspectionRequest']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(InspectionRequestEntity::class))
            ->andReturnUsing(
                function (InspectionRequestEntity $lic) use (&$inspectionRequest) {
                    $lic->setId(111);
                    $inspectionRequest = $lic;
                }
            );
        return $command;
    }
}
