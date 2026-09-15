<?php

declare(strict_types=1);

/**
 * Generate Licence Number Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\LicenceNoGen;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\GenerateLicenceNumber;
use Dvsa\Olcs\Api\Domain\Command\Application\GenerateLicenceNumber as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Licence\LicenceNoGen as LicenceNoGenEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Generate Licence Number Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GenerateLicenceNumberTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GenerateLicenceNumber();
        $this->mockRepo('Application', Application::class);
        $this->mockRepo('LicenceNoGen', LicenceNoGen::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE,
            LicenceEntity::LICENCE_CATEGORY_PSV
        ];

        $this->references = [
            TrafficArea::class => [
                TrafficArea::NORTH_EASTERN_TRAFFIC_AREA_CODE => m::mock(TrafficArea::class)
            ]
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('cantGenerateProvider')]
    public function testHandleCommandCantGenerate(\Closure $createApplication): void
    {
        $application = $createApplication();

        $data = [
            'id' => 111
        ];
        $command = Cmd::create($data);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();

        /** @var ApplicationEntity $application */
        $application->setLicence($licence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($application);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Can\'t generate licence number'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('gvOrPsvProvider')]
    public function testHandleCommandWithoutLicenceNo(\Closure $createApplication, mixed $expectedLicNo): void
    {
        $application = $createApplication();

        $data = [
            'id' => 111
        ];
        $command = Cmd::create($data);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setTrafficArea($this->references[TrafficArea::class][TrafficArea::NORTH_EASTERN_TRAFFIC_AREA_CODE]);

        /** @var ApplicationEntity $application */
        $application->setLicence($licence);

        $this->repoMap['LicenceNoGen']->shouldReceive('save')
            ->with(m::type(LicenceNoGenEntity::class))
            ->andReturnUsing(
                function (LicenceNoGenEntity $licNoGen) {
                    $licNoGen->setId(12345678);
                }
            );

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'licenceNumber' => $expectedLicNo
            ],
            'messages' => [
                'Licence number generated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithLicenceNo(): void
    {
        $data = [
            'id' => 111
        ];
        $command = Cmd::create($data);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setTrafficArea($this->references[TrafficArea::class][TrafficArea::NORTH_EASTERN_TRAFFIC_AREA_CODE]);
        $licence->setLicNo('OZ12345678');

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setGoodsOrPsv($this->refData[LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE]);
        $application->setLicence($licence);

        $this->repoMap['LicenceNoGen']->shouldReceive('save')
            ->with(m::type(LicenceNoGenEntity::class))
            ->andReturnUsing(
                function (LicenceNoGenEntity $licNoGen) {
                    $licNoGen->setId(12345678);
                }
            );

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'licenceNumber' => 'OB12345678'
            ],
            'messages' => [
                'Licence number updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithoutChange(): void
    {
        $data = [
            'id' => 111
        ];
        $command = Cmd::create($data);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setTrafficArea($this->references[TrafficArea::class][TrafficArea::NORTH_EASTERN_TRAFFIC_AREA_CODE]);
        $licence->setLicNo('OB12345678');

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setGoodsOrPsv($this->refData[LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE]);
        $application->setLicence($licence);

        $this->repoMap['LicenceNoGen']->shouldReceive('save')
            ->with(m::type(LicenceNoGenEntity::class))
            ->andReturnUsing(
                function (LicenceNoGenEntity $licNoGen) {
                    $licNoGen->setId(12345678);
                }
            );

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($application);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Licence number is unchanged'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function cantGenerateProvider(): array
    {
        return [
            [
                static fn () => m::mock(ApplicationEntity::class)->makePartial()
            ],
            [
                static fn () => self::applicationWithGoodsOrPsv(LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE)
            ]
        ];
    }

    public static function gvOrPsvProvider(): array
    {
        return [
            [
                static fn () => self::applicationWithGoodsOrPsv(LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE),
                'OB12345678'
            ],
            [
                static fn () => self::applicationWithGoodsOrPsv(LicenceEntity::LICENCE_CATEGORY_PSV),
                'PB12345678'
            ]
        ];
    }

    private static function applicationWithGoodsOrPsv(string $goodsOrPsv): ApplicationEntity
    {
        $refData = m::mock(RefData::class)->makePartial()->setId($goodsOrPsv);

        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setGoodsOrPsv($refData);

        return $application;
    }
}
