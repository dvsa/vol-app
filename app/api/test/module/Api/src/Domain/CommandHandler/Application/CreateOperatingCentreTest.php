<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\SetDefaultTrafficAreaAndEnforcementArea;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\CreateOperatingCentre as CommandHandler;
use Dvsa\Olcs\Api\Domain\Service\OperatingCentreHelper;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\Application\CreateOperatingCentre as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\User\Permission;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\Command\Application\HandleOcVariationFees as HandleOcVariationFeesCmd;

/**
 * Create Operating Centre Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateOperatingCentreTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('Document', Repository\Document::class);
        $this->mockRepo('OperatingCentre', Repository\OperatingCentre::class);
        $this->mockRepo('ApplicationOperatingCentre', Repository\ApplicationOperatingCentre::class);

        $this->mockedSmServices['OperatingCentreHelper'] = m::mock(OperatingCentreHelper::class);
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $applicationId = 111;
        $data = [
            'application' => $applicationId
        ];
        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial()
            ->shouldReceive('isVariation')
            ->andReturn(true)
            ->once()
            ->getMock();

        $application->initCollections();
        $application->setLicence($licence);
        $application->setId($applicationId);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with($applicationId)
            ->andReturn($application);

        /** @var OperatingCentre $oc */
        $oc = m::mock(OperatingCentre::class)->makePartial();
        $oc->setId(222);

        $this->mockedSmServices['OperatingCentreHelper']->shouldReceive('validate')
            ->once()
            ->with($application, $command, false)
            ->shouldReceive('createOperatingCentre')
            ->once()
            ->with($command, $this->commandHandler, m::type(Result::class), $this->repoMap['OperatingCentre'])
            ->andReturn($oc)
            ->shouldReceive('saveDocuments')
            ->with($application, $oc, $this->repoMap['Document'])
            ->shouldReceive('updateOperatingCentreLink')
            ->once()
            ->with(
                m::type(ApplicationOperatingCentre::class),
                $application,
                $command,
                $this->repoMap['ApplicationOperatingCentre']
            );

        $data = [
            'id' => $applicationId,
            'operatingCentre' => 222
        ];
        $result2 = new Result();
        $result2->addMessage('SetDefaultTrafficAreaAndEnforcementArea');
        $this->expectedSideEffect(SetDefaultTrafficAreaAndEnforcementArea::class, $data, $result2);

        $data = [
            'id' => $applicationId,
            'section' => 'operatingCentres'
        ];
        $result1 = new Result();
        $result1->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result1);

        $this->expectedSideEffect(HandleOcVariationFeesCmd::class, ['id' => $applicationId], new Result());

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(1, $application->getOperatingCentres()->count());

        /** @var ApplicationOperatingCentre $savedAoc */
        $savedAoc = $application->getOperatingCentres()->first();
        $savedOc = $savedAoc->getOperatingCentre();

        $this->assertSame($oc, $savedOc);

        $expected = [
            'id' => [],
            'messages' => [
                'SetDefaultTrafficAreaAndEnforcementArea',
                'UpdateApplicationCompletion'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
