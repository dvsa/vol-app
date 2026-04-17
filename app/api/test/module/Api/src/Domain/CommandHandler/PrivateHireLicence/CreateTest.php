<?php

declare(strict_types=1);

/**
 * CreateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\PrivateHireLicence;

use Dvsa\Olcs\Api\Domain\CommandHandler\PrivateHireLicence\Create as CommandHandler;
use Dvsa\Olcs\Api\Service\AddressHelper\AddressHelperService;
use Dvsa\Olcs\Transfer\Command\PrivateHireLicence\Create as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\User\Permission;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * CreateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('PrivateHireLicence', \Dvsa\Olcs\Api\Domain\Repository\PrivateHireLicence::class);
        $this->mockRepo('ContactDetails', \Dvsa\Olcs\Api\Domain\Repository\ContactDetails::class);
        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
            AddressHelperService::class => m::mock(AddressHelperService::class),
        ];

        $this->sut = new CommandHandler($this->mockedSmServices[AddressHelperService::class]);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = ['ct_hackney'];

        $this->references = [
            Country::class => [
                'CC' => m::mock(Country::class)
            ],
            Licence::class => [
                323 => m::mock(Licence::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $params = [
            'licence' => 323,
            'privateHireLicenceNo' => 'TOPDOG 1',
            'councilName' => 'Leeds',
            'address' => [
                'addressLine1' => 'LINE 1',
                'addressLine2' => 'LINE 2',
                'addressLine3' => 'LINE 3',
                'addressLine4' => 'LINE 4',
                'town' => 'TOWN',
                'postcode' => 'S1 4QT',
                'countryCode' => 'CC',
            ],
            'lva' => 'licence'
        ];
        $command = Command::create($params);

        $this->mockedSmServices[AddressHelperService::class]->shouldReceive('fetchTrafficAreaByPostcodeOrUprn')
            ->with('S1 4QT')->once()->andReturn(null);

        $this->repoMap['ContactDetails']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails $cd) use ($params) {
                $cd->setId(648);
                $this->assertSame($params['councilName'], $cd->getDescription());
                $this->assertSame($this->refData['ct_hackney'], $cd->getContactType());
                $cd->getAddress()->setId(45);
                $this->assertSame($params['address']['addressLine1'], $cd->getAddress()->getAddressLine1());
                $this->assertSame($params['address']['addressLine2'], $cd->getAddress()->getAddressLine2());
                $this->assertSame($params['address']['addressLine3'], $cd->getAddress()->getAddressLine3());
                $this->assertSame($params['address']['addressLine4'], $cd->getAddress()->getAddressLine4());
                $this->assertSame($params['address']['town'], $cd->getAddress()->getTown());
                $this->assertSame($params['address']['postcode'], $cd->getAddress()->getPostcode());
                $this->assertSame($this->references[Country::class]['CC'], $cd->getAddress()->getCountryCode());
            }
        );

        $this->repoMap['PrivateHireLicence']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Licence\PrivateHireLicence $phl) use ($params) {
                $phl->setId(7);
                $this->assertSame($params['privateHireLicenceNo'], $phl->getPrivateHireLicenceNo());
                $this->assertSame($this->references[Licence::class][323], $phl->getLicence());
                $this->assertSame(648, $phl->getContactDetails()->getId());
            }
        );

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(true);

        $data = [
            'licence' => 323,
            'category' => CategoryEntity::CATEGORY_APPLICATION,
            'subCategory' => CategoryEntity::TASK_SUB_CATEGORY_CHANGE_TO_TAXI_PHV_DIGITAL,
            'description' => 'Taxi licence added - ' . 'TOPDOG 1',
            'isClosed' => 0,
            'urgent' => 0,
            'actionDate' => null,
            'assignedToUser' => null,
            'assignedToTeam' => null,
            'application' => null,
            'busReg' => null,
            'case' => null,
            'transportManager' => null,
            'irfoOrganisation' => null
        ];
        $this->expectedSideEffect(CreateTaskCmd::class, $data, new Result());

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['address' => 45, 'contactDetails' => 648, 'privateHireLicence' => 7], $response->getIds());
        $this->assertSame(['PrivateHireLicence created'], $response->getMessages());
    }

    public function testHandleCommandUpdateTrafficArea(): void
    {
        $params = [
            'licence' => 323,
            'privateHireLicenceNo' => 'TOPDOG 1',
            'councilName' => 'Leeds',
            'address' => [
                'addressLine1' => 'LINE 1',
                'addressLine2' => 'LINE 2',
                'addressLine3' => 'LINE 3',
                'addressLine4' => 'LINE 4',
                'town' => 'TOWN',
                'postcode' => 'S1 4QT',
                'countryCode' => 'CC',
            ],
            'lva' => 'licence'
        ];
        $command = Command::create($params);

        $trafficArea = new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea();
        $trafficArea->setId('TA');

        $this->mockedSmServices[AddressHelperService::class]->shouldReceive('fetchTrafficAreaByPostcodeOrUprn')
            ->with('S1 4QT')->once()->andReturn($trafficArea);

        $this->repoMap['ContactDetails']->shouldReceive('save')->once();

        $this->repoMap['PrivateHireLicence']->shouldReceive('save')->once();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(false);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Licence\UpdateTrafficArea::class,
            ['id' => 323, 'version' => 1, 'trafficArea' => 'TA'],
            new Result()
        );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['PrivateHireLicence created'], $response->getMessages());
    }

    public function testHandleCommandUpdateTrafficAreaValidationError(): void
    {
        $params = [
            'licence' => 323,
            'privateHireLicenceNo' => 'TOPDOG 1',
            'councilName' => 'Leeds',
            'address' => [
                'addressLine1' => 'LINE 1',
                'addressLine2' => 'LINE 2',
                'addressLine3' => 'LINE 3',
                'addressLine4' => 'LINE 4',
                'town' => 'TOWN',
                'postcode' => 'S1 4QT',
                'countryCode' => 'CC',
            ],
            'lva' => 'licence'
        ];
        $command = Command::create($params);

        $trafficArea = new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea();
        $trafficArea->setId('TA');

        $trafficArea2 = new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea();
        $trafficArea2->setId('TA2');

        $this->references[Licence::class][323]->setTrafficArea($trafficArea2);

        $this->mockedSmServices[AddressHelperService::class]->shouldReceive('fetchTrafficAreaByPostcodeOrUprn')
            ->with('S1 4QT')->once()->andReturn($trafficArea);

        try {
            $this->sut->handleCommand($command);
            $this->fail('Exception should have been thrown');
        } catch (\Dvsa\Olcs\Api\Domain\Exception\ValidationException $e) {
            $this->assertArrayHasKey('PHL_INVALID_TA', $e->getMessages());
        }
    }
}
