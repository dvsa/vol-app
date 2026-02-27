<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CommonGrant;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CopyApplicationDataToLicence;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CreateDiscRecords;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\ProcessApplicationOperatingCentres;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\GrantPsv;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Application\GrantPsv as Cmd;
use Dvsa\Olcs\Api\Domain\Command\ConditionUndertaking\CreateSmallVehicleCondition as CreateSvConditionUndertakingCmd;

/**
 * Grant Psv Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GrantPsvTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GrantPsv();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        $this->mockedSmServices = [
            \LmcRbacMvc\Service\AuthorizationService::class => m::mock(\LmcRbacMvc\Service\AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            Licence::LICENCE_STATUS_VALID,
            ApplicationEntity::APPLICATION_STATUS_VALID
        ];
        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111
        ];

        $command = Cmd::create($data);

        $this->setupIsInternalUser(false);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->updateTotAuthHgvVehicles(10);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setLicence($licence);
        $application->shouldReceive('isSpecialRestricted')
            ->andReturn(false);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $result1 = new Result();
        $result1->addMessage('CreateSnapshot');
        $this->expectedSideEffectAsSystemUser(
            CreateSnapshot::class,
            ['id' => 111, 'event' => CreateSnapshot::ON_GRANT],
            $result1
        );

        $result2 = new Result();
        $result2->addMessage('CopyApplicationDataToLicence');
        $this->expectedSideEffectAsSystemUser(CopyApplicationDataToLicence::class, $data, $result2);

        $result3 = new Result();
        $result3->addMessage('CreateDiscRecords');
        $discData = $data;
        $discData['currentTotAuth'] = 10;
        $this->expectedSideEffectAsSystemUser(CreateDiscRecords::class, $discData, $result3);

        $result4 = new Result();
        $result4->addMessage('ProcessApplicationOperatingCentres');
        $this->expectedSideEffectAsSystemUser(ProcessApplicationOperatingCentres::class, $data, $result4);

        $result5 = new Result();
        $result5->addMessage('CommonGrant');
        $this->expectedSideEffectAsSystemUser(CommonGrant::class, $data, $result5);

        $this->expectedSideEffectAsSystemUser(
            CreateSvConditionUndertakingCmd::class,
            ['applicationId' => 111],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'CreateSnapshot',
                'Application status updated',
                'Licence status updated',
                'CopyApplicationDataToLicence',
                'CreateDiscRecords',
                'ProcessApplicationOperatingCentres',
                'CommonGrant'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(Licence::LICENCE_STATUS_VALID, $licence->getStatus()->getId());
        $this->assertEquals(ApplicationEntity::APPLICATION_STATUS_VALID, $application->getStatus()->getId());
    }

    public function testHandleCommandCloseTasks(): void
    {
        $data = [
            'id' => 111
        ];

        $command = Cmd::create($data);

        $this->setupIsInternalUser(true);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->updateTotAuthHgvVehicles(10);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setLicence($licence);
        $application->shouldReceive('isSpecialRestricted')
            ->andReturn(false);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $result1 = new Result();
        $result1->addMessage('CreateSnapshot');
        $this->expectedSideEffectAsSystemUser(
            CreateSnapshot::class,
            ['id' => 111, 'event' => CreateSnapshot::ON_GRANT],
            $result1
        );

        $result2 = new Result();
        $result2->addMessage('CopyApplicationDataToLicence');
        $this->expectedSideEffectAsSystemUser(CopyApplicationDataToLicence::class, $data, $result2);

        $result3 = new Result();
        $result3->addMessage('CreateDiscRecords');
        $discData = $data;
        $discData['currentTotAuth'] = 10;
        $this->expectedSideEffectAsSystemUser(CreateDiscRecords::class, $discData, $result3);

        $result4 = new Result();
        $result4->addMessage('ProcessApplicationOperatingCentres');
        $this->expectedSideEffectAsSystemUser(ProcessApplicationOperatingCentres::class, $data, $result4);

        $result5 = new Result();
        $result5->addMessage('CommonGrant');
        $this->expectedSideEffect(CommonGrant::class, $data, $result5);

        $this->expectedSideEffectAsSystemUser(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::class,
            ['id' => 111],
            (new Result())->addMessage('CLOSE_TEX_TASK')
        );
        $this->expectedSideEffectAsSystemUser(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseFeeDueTask::class,
            ['id' => 111],
            (new Result())->addMessage('CLOSE_FEEDUE_TASK')
        );

        $this->expectedSideEffectAsSystemUser(
            CreateSvConditionUndertakingCmd::class,
            ['applicationId' => 111],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'CreateSnapshot',
                'Application status updated',
                'Licence status updated',
                'CopyApplicationDataToLicence',
                'CreateDiscRecords',
                'ProcessApplicationOperatingCentres',
                'CLOSE_TEX_TASK',
                'CLOSE_FEEDUE_TASK',
                'CommonGrant',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(Licence::LICENCE_STATUS_VALID, $licence->getStatus()->getId());
        $this->assertEquals(ApplicationEntity::APPLICATION_STATUS_VALID, $application->getStatus()->getId());
    }
}
