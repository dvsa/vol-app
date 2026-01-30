<?php

/**
 * Update Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceOperatingCentre;

use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Command\LicenceOperatingCentre\Update as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\LicenceOperatingCentre\Update as CommandHandler;
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
        $this->mockRepo('LicenceOperatingCentre', Repository\LicenceOperatingCentre::class);
        $this->mockRepo('Document', Repository\Document::class);
        $this->mockRepo('OperatingCentre', Repository\OperatingCentre::class);
        $this->mockedSmServices ['EventHistoryCreator'] = m::mock(EventHistoryCreator::class);

        $this->mockedSmServices['OperatingCentreHelper'] = m::mock(OperatingCentreHelper::class);
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

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
                'addressLine1' => '123 Street'
            ]
        ];
        $command = Cmd::create($data);

        /** @var OperatingCentre $oc */
        $oc = m::mock(OperatingCentre::class)->makePartial();

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $loc->setLicence($licence);
        $loc->setOperatingCentre($oc);

        $this->repoMap['LicenceOperatingCentre']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($loc);

        $this->mockedSmServices['OperatingCentreHelper']->shouldReceive('validate')
            ->once()
            ->with($licence, $command, false, $loc)
            ->shouldReceive('saveDocuments')
            ->once()
            ->with($licence, $oc, $this->repoMap['Document'])
            ->shouldReceive('updateOperatingCentreLink')
            ->once()
            ->with($loc, $licence, $command, $this->repoMap['LicenceOperatingCentre']);

        $data = [
            'id' => null,
            'version' => null,
            'addressLine1' => '123 Street',
            'addressLine2' => null,
            'addressLine3' => null,
            'addressLine4' => null,
            'town' => null,
            'postcode' => null,
            'countryCode' => null,
            'contactType' => null
        ];
        $result1 = new Result();
        $result1->addMessage('SaveAddress');
        $this->expectedSideEffect(SaveAddress::class, $data, $result1);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('isGranted')
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false)
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'SaveAddress'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandCreatesEventHistoryWhenVersionChanges()
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'address' => [
                'addressLine1' => '123 Street',
                'version' => 1,
            ]
        ];

        $command = Cmd::create($data);

        $addressEntity = m::mock(Address::class);
        $addressEntity->shouldReceive('getVersion')->andReturn(2);

        $oc = m::mock(OperatingCentre::class)->makePartial();
        $oc->shouldReceive('getAddress')->andReturn($addressEntity);

        $licence = m::mock(Licence::class)->makePartial();

        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $loc->setLicence($licence);
        $loc->setOperatingCentre($oc);

        $this->repoMap['LicenceOperatingCentre']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($loc);

        $this->mockedSmServices['OperatingCentreHelper']
            ->shouldReceive('validate')
            ->once()
            ->with($licence, $command, false, $loc);

        $this->mockedSmServices['OperatingCentreHelper']
            ->shouldReceive('saveDocuments')
            ->once();

        $this->mockedSmServices['OperatingCentreHelper']
            ->shouldReceive('updateOperatingCentreLink')
            ->once();

        $expectedSaveAddressData = [
            'id' => null,
            'version' => 1,
            'addressLine1' => '123 Street',
            'addressLine2' => null,
            'addressLine3' => null,
            'addressLine4' => null,
            'town' => null,
            'postcode' => null,
            'countryCode' => null,
            'contactType' => null,
        ];

        $result1 = new Result();
        $result1->addMessage('SaveAddress');

        $this->expectedSideEffect(
            SaveAddress::class,
            $expectedSaveAddressData,
            $result1
        );

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with($oc->getAddress(), EventHistoryTypeEntity::EVENT_CODE_EDIT_OPERATING_CENTRE, null, $licence)
            ->once();
        
        $this->mockedSmServices[AuthorizationService::class]
                ->shouldReceive('isGranted')
                ->with(Permission::SELFSERVE_USER, null)
                ->andReturn(false)
                ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            ['SaveAddress'],
            $result->getMessages()
        );
    }
}
