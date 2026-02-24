<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Licence as  LicenceRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\CreateGoodsVehicle;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\Licence\CreateGoodsVehicle as Cmd;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\CreateGoodsVehicle as VehicleCmd;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\CreateGoodsDiscs as CreateGoodsDiscsCmd;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Create Goods Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateGoodsVehicleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateGoodsVehicle();
        $this->mockRepo('Licence', LicenceRepo::class);

        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommandWithoutSpaces(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'id' => 111
        ];
        $command = Cmd::create($data);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('getRemainingSpaces')
            ->andReturn(0);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommand(): void
    {
        $this->mockedSmServices[AuthorizationService::class]->expects('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturnTrue();

        $data = [
            'id' => 111,
            'vrm' => 'ABC123',
            'platedWeight' => 100,
            'specifiedDate' => null,
            'receivedDate' => null,
            'confirm' => 1
        ];
        $command = Cmd::create($data);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('getRemainingSpaces')
            ->andReturn(1);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence);

        $data = [
            'licence' => 111,
            'vrm' => 'ABC123',
            'platedWeight' => 100,
            'specifiedDate' => (new DateTime('now'))->format(\DateTime::ISO8601),
            'receivedDate' => null,
            'confirm' => 1
        ];
        $result1 = new Result();
        $result1->addId('licenceVehicle', 123);
        $result1->addMessage('Licence Vehicle Created');
        $this->expectedSideEffect(VehicleCmd::class, $data, $result1);

        $data = [
            'ids' => [123],
            'isCopy' => 'N'
        ];
        $result2 = new Result();
        $result2->addId('goodsDisc', 321);
        $result2->addMessage('Goods Disc Created');
        $this->expectedSideEffect(CreateGoodsDiscsCmd::class, $data, $result2);

        $this->expectedLicenceCacheClear($licence);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'licenceVehicle' => 123,
                'goodsDisc' => 321
            ],
            'messages' => [
                'Licence Vehicle Created',
                'Goods Disc Created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
