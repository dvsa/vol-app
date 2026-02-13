<?php

/**
 * Update Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationOperatingCentre;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Command\ApplicationOperatingCentre\Update as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationOperatingCentre\Update as CommandHandler;
use Dvsa\Olcs\Api\Domain\Service\OperatingCentreHelper;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;

/**
 * Update Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ApplicationOperatingCentre', Repository\ApplicationOperatingCentre::class);
        $this->mockRepo('Document', Repository\Document::class);
        $this->mockRepo('OperatingCentre', Repository\OperatingCentre::class);

        $this->mockedSmServices['OperatingCentreHelper'] = m::mock(OperatingCentreHelper::class);
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);
        $this->mockedSmServices ['EventHistoryCreator'] = m::mock(EventHistoryCreator::class);

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [];

        parent::initReferences();
    }

    public function testHandleCommand()
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'address' => [
                'addressLine1' => '123 Street',
                'postcode' => 'SM1 7ZZ',
                'version' => 1
            ]
        ];
        $command = Cmd::create($data);

        /* @var $oc OperatingCentre */
        $oc = m::mock(OperatingCentre::class)->makePartial();
        $oc->setId(333);

        $addressEntity = new \Dvsa\Olcs\Api\Entity\ContactDetails\Address();
        $addressEntity->setVersion(2);
        $oc->setAddress($addressEntity);

        $application = $this->getTestingApplication();
        $application->setId(222);

        /* @var $aoc ApplicationOperatingCentre */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setApplication($application);
        $aoc->setOperatingCentre($oc);
        $application->addOperatingCentres($aoc);

        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($aoc);

        $this->mockedSmServices['OperatingCentreHelper']->shouldReceive('validate')
            ->once()
            ->with($application, $command, false, $aoc)
            ->shouldReceive('saveDocuments')
            ->once()
            ->with($application, $oc, $this->repoMap['Document'])
            ->shouldReceive('updateOperatingCentreLink')
            ->once()
            ->with($aoc, $application, $command, $this->repoMap['ApplicationOperatingCentre']);

        $dataSideEffect = [
            'id' => null,
            'version' => 1,
            'addressLine1' => '123 Street',
            'addressLine2' => null,
            'addressLine3' => null,
            'addressLine4' => null,
            'town' => null,
            'postcode' => 'SM1 7ZZ',
            'countryCode' => null,
            'contactType' => null
        ];
        $result1 = new Result();
        $result1->addMessage('SaveAddress');
        $this->expectedSideEffect(SaveAddress::class, $dataSideEffect, $result1);

        $completionData = [
            'id' => $application->getId(),
            'section' => 'operatingCentres'
        ];
        $result2 = new Result();
        $result2->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $completionData, $result2);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false)
            ->once();

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\SetDefaultTrafficAreaAndEnforcementArea::class,
            ['id' => 222, 'operatingCentre' => 333],
            (new Result())->addMessage('SET_TA')
        );

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with($oc->getAddress(), EventHistoryTypeEntity::EVENT_CODE_EDIT_OPERATING_CENTRE, null, $application->getLicence())
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'SaveAddress',
                'SET_TA',
                'UpdateApplicationCompletion'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
