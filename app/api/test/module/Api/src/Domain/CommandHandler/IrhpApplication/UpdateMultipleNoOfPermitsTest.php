<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\IrhpApplication\RegenerateApplicationFee;
use Dvsa\Olcs\Api\Domain\Command\IrhpApplication\RegenerateIssueFee;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\UpdateMultipleNoOfPermits;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepo;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\MaxStockPermitsByApplication;
use Mockery as m;
use RuntimeException;

class UpdateMultipleNoOfPermitsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpPermitApplication', IrhpPermitApplicationRepo::class);

        $this->sut = m::mock(UpdateMultipleNoOfPermits::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        parent::setUp();
    }

    public function testIsNotReadyForNoOfPermits(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('IRHP application is not ready for number of permits');

        $irhpApplicationId = 44;

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isReadyForNoOfPermits')
            ->andReturn(false);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($irhpApplicationId);

        $this->sut->handleCommand($command);
    }

    public function testMultilateralFeesRequiredChanged(): void
    {
        $irhpApplicationId = 44;

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getId')
            ->andReturn($irhpApplicationId);
        $irhpApplication->shouldReceive('getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);
        $irhpApplication->shouldReceive('getPermitsRequired')
            ->andReturn(17);
        $irhpApplication->shouldReceive('storeFeesRequired')
            ->once()
            ->globally()
            ->ordered();
        $irhpApplication->shouldReceive('isReadyForNoOfPermits')
            ->andReturn(true);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);

        $irhpPermitApplication2019 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2019->shouldReceive('updatePermitsRequired')
            ->with(2)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpPermitApplication']->shouldReceive('saveOnFlush')
            ->with($irhpPermitApplication2019)
            ->once()
            ->globally()
            ->ordered();

        $irhpPermitApplication2020 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2020->shouldReceive('updatePermitsRequired')
            ->with(4)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpPermitApplication']->shouldReceive('saveOnFlush')
            ->with($irhpPermitApplication2020)
            ->once()
            ->globally()
            ->ordered();

        $irhpApplication->shouldReceive('resetCheckAnswersAndDeclaration')
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpApplication']->shouldReceive('saveOnFlush')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpPermitApplication']->shouldReceive('flushAll')
            ->once()
            ->globally()
            ->ordered();

        $irhpApplication->shouldReceive('haveFeesRequiredChanged')
            ->once()
            ->globally()
            ->ordered()
            ->andReturn(true);

        $this->expectedSideEffect(
            RegenerateApplicationFee::class,
            ['id' => $irhpApplicationId],
            new Result()
        );

        $this->expectedSideEffect(
            RegenerateIssueFee::class,
            ['id' => $irhpApplicationId],
            new Result()
        );

        $this->sut->shouldReceive('handleQuery')
            ->andReturnUsing(function ($query) use ($irhpApplicationId) {
                $this->assertInstanceOf(MaxStockPermitsByApplication::class, $query);
                $this->assertEquals($irhpApplicationId, $query->getId());

                return [
                    'result' => [
                        7 => 12,
                        8 => 4,
                    ]
                ];
            });

        $irhpApplicationWithStockInfoResponse = [
            [
                'irhpPermitApplication' => $irhpPermitApplication2019,
                'validTo' => '2019-12-31',
                'countryId' => null,
                'stockId' => 7
            ],
            [
                'irhpPermitApplication' => $irhpPermitApplication2020,
                'validTo' => '2020-12-31',
                'countryId' => null,
                'stockId' => 8
            ],
        ];

        $this->repoMap['IrhpPermitApplication']->shouldReceive('getByIrhpApplicationWithStockInfo')
            ->andReturn($irhpApplicationWithStockInfoResponse);

        $commandPermitsRequired = [
            2019 => 2,
            2020 => 4,
        ];

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($irhpApplicationId);
        $command->shouldReceive('getPermitsRequired')
            ->andReturn($commandPermitsRequired);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($irhpApplicationId, $result->getId('irhpApplication'));
        $this->assertEquals(
            [
                'Updated 2 required permit counts for IRHP application'
            ],
            $result->getMessages()
        );
    }

    public function testMultilateralFeesRequiredNotChanged(): void
    {
        $irhpApplicationId = 44;

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getId')
            ->andReturn($irhpApplicationId);
        $irhpApplication->shouldReceive('getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);
        $irhpApplication->shouldReceive('storeFeesRequired')
            ->once()
            ->globally()
            ->ordered();
        $irhpApplication->shouldReceive('isReadyForNoOfPermits')
            ->andReturn(true);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);

        $irhpPermitApplication2019 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2019->shouldReceive('updatePermitsRequired')
            ->with(2)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpPermitApplication']->shouldReceive('saveOnFlush')
            ->with($irhpPermitApplication2019)
            ->once()
            ->globally()
            ->ordered();

        $irhpPermitApplication2020 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2020->shouldReceive('updatePermitsRequired')
            ->with(4)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpPermitApplication']->shouldReceive('saveOnFlush')
            ->with($irhpPermitApplication2020)
            ->once()
            ->globally()
            ->ordered();

        $irhpApplication->shouldReceive('resetCheckAnswersAndDeclaration')
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpApplication']->shouldReceive('saveOnFlush')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpPermitApplication']->shouldReceive('flushAll')
            ->once()
            ->globally()
            ->ordered();

        $irhpApplication->shouldReceive('haveFeesRequiredChanged')
            ->once()
            ->globally()
            ->ordered()
            ->andReturn(false);

        $this->sut->shouldReceive('handleQuery')
            ->andReturnUsing(function ($query) use ($irhpApplicationId) {
                $this->assertInstanceOf(MaxStockPermitsByApplication::class, $query);
                $this->assertEquals($irhpApplicationId, $query->getId());

                return [
                    'result' => [
                        7 => 12,
                        8 => 4,
                    ]
                ];
            });

        $irhpApplicationWithStockInfoResponse = [
            [
                'irhpPermitApplication' => $irhpPermitApplication2019,
                'validTo' => '2019-12-31',
                'countryId' => null,
                'stockId' => 7
            ],
            [
                'irhpPermitApplication' => $irhpPermitApplication2020,
                'validTo' => '2020-12-31',
                'countryId' => null,
                'stockId' => 8
            ],
        ];

        $this->repoMap['IrhpPermitApplication']->shouldReceive('getByIrhpApplicationWithStockInfo')
            ->andReturn($irhpApplicationWithStockInfoResponse);

        $commandPermitsRequired = [
            2019 => 2,
            2020 => 4,
        ];

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($irhpApplicationId);
        $command->shouldReceive('getPermitsRequired')
            ->andReturn($commandPermitsRequired);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($irhpApplicationId, $result->getId('irhpApplication'));
        $this->assertEquals(
            [
                'Updated 2 required permit counts for IRHP application'
            ],
            $result->getMessages()
        );
    }

    public static function dpUnsupportedPermitType(): array
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpUnsupportedPermitType')]
    public function testUnsupportedPermitType(mixed $irhpPermitTypeId): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Unsupported permit type ' . $irhpPermitTypeId
        );

        $irhpApplicationId = 44;

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getId')
            ->andReturn($irhpApplicationId);
        $irhpApplication->shouldReceive('getIrhpPermitType->getId')
            ->andReturn($irhpPermitTypeId);
        $irhpApplication->shouldReceive('storeFeesRequired')
            ->once();
        $irhpApplication->shouldReceive('isReadyForNoOfPermits')
            ->andReturn(true);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);

        $this->sut->shouldReceive('handleQuery')
            ->andReturnUsing(function ($query) use ($irhpApplicationId) {
                $this->assertInstanceOf(MaxStockPermitsByApplication::class, $query);
                $this->assertEquals($irhpApplicationId, $query->getId());

                return [
                    'result' => [
                        7 => 12,
                        8 => 4,
                    ]
                ];
            });

        $irhpPermitApplication2019 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2020 = m::mock(IrhpPermitApplication::class);

        $irhpApplicationWithStockInfoResponse = [
            [
                'irhpPermitApplication' => $irhpPermitApplication2019,
                'validTo' => '2019-12-31',
                'countryId' => null,
                'stockId' => 7
            ],
            [
                'irhpPermitApplication' => $irhpPermitApplication2020,
                'validTo' => '2020-12-31',
                'countryId' => null,
                'stockId' => 8
            ],
        ];

        $this->repoMap['IrhpPermitApplication']->shouldReceive('getByIrhpApplicationWithStockInfo')
            ->andReturn($irhpApplicationWithStockInfoResponse);

        $commandPermitsRequired = [
            2019 => 2,
            2020 => 7,
        ];

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($irhpApplicationId);
        $command->shouldReceive('getPermitsRequired')
            ->andReturn($commandPermitsRequired);

        $this->sut->handleCommand($command);
    }

    public function testMultilateralPermitsRequiredOutOfRange(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Out of range data for stock id 8 - expected range 0 to 4 but received 7'
        );

        $irhpApplicationId = 44;

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getId')
            ->andReturn($irhpApplicationId);
        $irhpApplication->shouldReceive('getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);
        $irhpApplication->shouldReceive('storeFeesRequired')
            ->once()
            ->globally()
            ->ordered();
        $irhpApplication->shouldReceive('resetCheckAnswersAndDeclaration')
            ->never();
        $irhpApplication->shouldReceive('isReadyForNoOfPermits')
            ->andReturn(true);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);

        $this->repoMap['IrhpApplication']->shouldReceive('saveOnFlush')
            ->with($irhpApplication)
            ->never();

        $irhpPermitApplication2019 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2019->shouldReceive('updatePermitsRequired')
            ->with(2)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpPermitApplication']->shouldReceive('saveOnFlush')
            ->with($irhpPermitApplication2019)
            ->once()
            ->globally()
            ->ordered();

        $irhpPermitApplication2020 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2020->shouldReceive('updatePermitsRequired')
            ->never();

        $this->repoMap['IrhpPermitApplication']->shouldReceive('saveOnFlush')
            ->with($irhpPermitApplication2020)
            ->never();

        $irhpApplication->shouldReceive('haveFeesRequiredChanged')
            ->never();

        $this->sut->shouldReceive('handleQuery')
            ->andReturnUsing(function ($query) use ($irhpApplicationId) {
                $this->assertInstanceOf(MaxStockPermitsByApplication::class, $query);
                $this->assertEquals($irhpApplicationId, $query->getId());

                return [
                    'result' => [
                        7 => 12,
                        8 => 4,
                    ]
                ];
            });

        $irhpApplicationWithStockInfoResponse = [
            [
                'irhpPermitApplication' => $irhpPermitApplication2019,
                'validTo' => '2019-12-31',
                'countryId' => null,
                'stockId' => 7
            ],
            [
                'irhpPermitApplication' => $irhpPermitApplication2020,
                'validTo' => '2020-12-31',
                'countryId' => null,
                'stockId' => 8
            ],
        ];

        $this->repoMap['IrhpPermitApplication']->shouldReceive('getByIrhpApplicationWithStockInfo')
            ->andReturn($irhpApplicationWithStockInfoResponse);

        $commandPermitsRequired = [
            2019 => 2,
            2020 => 7,
        ];

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($irhpApplicationId);
        $command->shouldReceive('getPermitsRequired')
            ->andReturn($commandPermitsRequired);

        $this->sut->handleCommand($command);
    }

    public function testMultilateralMissingPermitsRequiredData(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Missing data or incorrect type for year 2020'
        );

        $irhpApplicationId = 44;

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getId')
            ->andReturn($irhpApplicationId);
        $irhpApplication->shouldReceive('getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);
        $irhpApplication->shouldReceive('storeFeesRequired')
            ->once()
            ->globally()
            ->ordered();
        $irhpApplication->shouldReceive('resetCheckAnswersAndDeclaration')
            ->never();
        $irhpApplication->shouldReceive('isReadyForNoOfPermits')
            ->andReturn(true);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);

        $this->repoMap['IrhpApplication']->shouldReceive('saveOnFlush')
            ->with($irhpApplication)
            ->never();

        $irhpPermitApplication2019 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2019->shouldReceive('updatePermitsRequired')
            ->with(2)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpPermitApplication']->shouldReceive('saveOnFlush')
            ->with($irhpPermitApplication2019)
            ->once()
            ->globally()
            ->ordered();

        $irhpPermitApplication2020 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2020->shouldReceive('updatePermitsRequired')
            ->never();

        $this->repoMap['IrhpPermitApplication']->shouldReceive('saveOnFlush')
            ->with($irhpPermitApplication2020)
            ->never();

        $this->repoMap['IrhpPermitApplication']->shouldReceive('flushAll')
            ->never();

        $irhpApplication->shouldReceive('haveFeesRequiredChanged')
            ->never();

        $this->sut->shouldReceive('handleQuery')
            ->andReturnUsing(function ($query) use ($irhpApplicationId) {
                $this->assertInstanceOf(MaxStockPermitsByApplication::class, $query);
                $this->assertEquals($irhpApplicationId, $query->getId());

                return [
                    'result' => [
                        7 => 12,
                        8 => 4,
                    ]
                ];
            });

        $irhpApplicationWithStockInfoResponse = [
            [
                'irhpPermitApplication' => $irhpPermitApplication2019,
                'validTo' => '2019-12-31',
                'countryId' => null,
                'stockId' => 7
            ],
            [
                'irhpPermitApplication' => $irhpPermitApplication2020,
                'validTo' => '2020-12-31',
                'countryId' => null,
                'stockId' => 8
            ],
        ];

        $this->repoMap['IrhpPermitApplication']->shouldReceive('getByIrhpApplicationWithStockInfo')
            ->andReturn($irhpApplicationWithStockInfoResponse);

        $commandPermitsRequired = [
            2019 => 2,
        ];

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($irhpApplicationId);
        $command->shouldReceive('getPermitsRequired')
            ->andReturn($commandPermitsRequired);

        $this->sut->handleCommand($command);
    }
}
