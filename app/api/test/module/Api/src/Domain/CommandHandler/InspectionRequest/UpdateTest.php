<?php

declare(strict_types=1);

/**
 * Inspection Request / Update
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\InspectionRequest;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\InspectionRequest\Update;
use Dvsa\Olcs\Api\Domain\Repository\InspectionRequest as InspectionRequestRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Update as Cmd;
use Dvsa\Olcs\Api\Entity\Inspection\InspectionRequest as InspectionRequestEntity;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * Inspection Request / Update
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Update();
        $this->mockRepo('InspectionRequest', InspectionRequestRepo::class);

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

        parent::initReferences();
    }

    public function testHandleCommandCreateFromApplication(): void
    {
        $this->mockAuthService();

        $id = 111;
        $data = [
            'id' => $id,
            'requestType' => InspectionRequestEntity::REQUEST_TYPE_NEW_OP,
            'requestDate' => '2015-01-01',
            'dueDate' => '2016-01-01',
            'resultType' => InspectionRequestEntity::RESULT_TYPE_NEW,
            'requestorNotes' => 'reqnotes',
            'reportType' => InspectionRequestEntity::REPORT_TYPE_MAINTENANCE_REQUEST,
            'inspectorName' => 'iname',
            'returnDate' => '2016-01-01',
            'fromDate' => '2014-01-01',
            'toDate' => '2015-01-01',
            'vehiclesExaminedNo' => 1,
            'trailersExaminedNo' => 2,
            'inspectorNotes' => 'inspnotes'
        ];

        $command = Cmd::create($data);

        $mockInspectionRequest = m::mock()
            ->shouldReceive('updateInspectionRequest')
            ->once()
            ->shouldReceive('getId')
            ->andReturn($id)
            ->once()
            ->getMock();

        $this->repoMap['InspectionRequest']
            ->shouldReceive('fetchById')
            ->with($id)
            ->andReturn($mockInspectionRequest)
            ->once()
            ->shouldReceive('save')
            ->with($mockInspectionRequest)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'inspectionRequest' => $id
            ],
            'messages' => [
                'Inspection request updated successfully',
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
