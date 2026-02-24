<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\PrintLicence;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Transfer\Command\Licence\PrintLicence as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Licence\PrintLicence::class)]
class PrintLicenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PrintLicence();
        $this->mockRepo('Licence', Licence::class);

        parent::setUp();
    }

    public function testHandleCommandFailNull(): void
    {
        $command = Cmd::create(['id' => 111]);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)->andReturnNull();

        static::assertNull($this->sut->handleCommand($command));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testHandleCommand(mixed $command, mixed $isGoods, mixed $vehicleType, mixed $isSpecialRestricted, mixed $niFlag, array $expect): void
    {
        /** @var LicenceEntity | m\MockInterface $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(111);
        $licence->shouldReceive('isGoods')->andReturn($isGoods);
        $licence->shouldReceive('isSpecialRestricted')->andReturn($isSpecialRestricted);
        $licence->shouldReceive('getNiFlag')->andReturn($niFlag);
        $licence->setVehicleType(new RefData($vehicleType));

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence);

        $data = [
            'template' => $expect['docId'],
            'query' => ['licence' => 111],
            'description' => $expect['desc'],
            'licence' => 111,
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS,
            'isExternal' => false,
            'application' => null,
            'busReg' => null,
            'case' => null,
            'irfoOrganisation' => null,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => null,
            'operatingCentre' => null,
            'opposition' => null,
            'isScan' => 0,
            'issuedDate' => null,
            'dispatch' => true,
        ];
        $result1 = new Result();
        $result1->addMessage('GenerateAndStore');
        $this->expectedSideEffect(GenerateAndStore::class, $data, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'GenerateAndStore',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function dataProvider(): array
    {
        $command = Cmd::create(['id' => 111]);

        return [
            'GB goods mixed fleet' => [
                'command' => $command,
                'isGoods' => true,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'isSpecialRestricted' => false,
                'niFlag' => 'N',
                'expect' => [
                    'docId' => Document::GV_LICENCE_GB,
                    'desc' => 'GV Licence',
                ],
            ],
            'GB goods lgv' => [
                'command' => $command,
                'isGoods' => true,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'isSpecialRestricted' => false,
                'niFlag' => 'N',
                'expect' => [
                    'docId' => Document::GV_LGV_LICENCE_GB,
                    'desc' => 'GV Licence LGV Only',
                ],
            ],
            'GB psv' => [
                'command' => $command,
                'isGoods' => false,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                'isSpecialRestricted' => false,
                'niFlag' => 'N',
                'expect' => [
                    'docId' => Document::PSV_LICENCE_GB,
                    'desc' => 'PSV Licence',
                ],
            ],
            'GB psv special restricted' => [
                'command' => $command,
                'isGoods' => false,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                'isSpecialRestricted' => true,
                'niFlag' => 'N',
                'expect' => [
                    'docId' => Document::PSR_SR_LICENCE_GB,
                    'desc' => 'PSV-SR Licence',
                ],
            ],
            'NI goods mixed fleet' => [
                'command' => $command,
                'isGoods' => true,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'isSpecialRestricted' => false,
                'niFlag' => 'Y',
                'expect' => [
                    'docId' => Document::GV_LICENCE_NI,
                    'desc' => 'GV Licence',
                ],
            ],
            'NI goods lgv' => [
                'command' => $command,
                'isGoods' => true,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'isSpecialRestricted' => false,
                'niFlag' => 'Y',
                'expect' => [
                    'docId' => Document::GV_LGV_LICENCE_GB,
                    'desc' => 'GV Licence LGV Only',
                ],
            ],
            'NI psv' => [
                'command' => $command,
                'isGoods' => false,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                'isSpecialRestricted' => false,
                'niFlag' => 'Y',
                'expect' => [
                    'docId' => Document::PSV_LICENCE_NI,
                    'desc' => 'PSV Licence',
                ],
            ],
            'NI psv special restricted' => [
                'command' => $command,
                'isGoods' => false,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                'isSpecialRestricted' => true,
                'niFlag' => 'Y',
                'expect' => [
                    'docId' => Document::PSR_SR_LICENCE_NI,
                    'desc' => 'PSV-SR Licence',
                ],
            ],
        ];
    }

    public function testHandleCommandDispatchFalse(): void
    {
        $command = Cmd::create(['id' => 111, 'dispatch' => false]);

        /** @var LicenceEntity | m\MockInterface $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(111);
        $licence->shouldReceive('isGoods')->andReturn(false);
        $licence->shouldReceive('isSpecialRestricted')->andReturn(true);
        $licence->shouldReceive('getNiFlag')->andReturn('Y');
        $licence->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_PSV));

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence);

        $data = [
            'template' => Document::PSR_SR_LICENCE_NI,
            'query' => ['licence' => 111],
            'description' => 'PSV-SR Licence',
            'licence' => 111,
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_OTHER_DOCUMENTS,
            'isExternal' => false,
            'application' => null,
            'busReg' => null,
            'case' => null,
            'irfoOrganisation' => null,
            'submission' => null,
            'trafficArea' => null,
            'transportManager' => null,
            'operatingCentre' => null,
            'opposition' => null,
            'isScan' => 0,
            'issuedDate' => null,
            'dispatch' => false,
        ];
        $result1 = new Result();
        $result1->addMessage('GenerateAndStore');
        $this->expectedSideEffect(GenerateAndStore::class, $data, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'GenerateAndStore',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
