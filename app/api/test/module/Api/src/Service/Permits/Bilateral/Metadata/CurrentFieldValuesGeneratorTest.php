<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Metadata;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata\CurrentFieldValuesGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CurrentFieldValuesGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CurrentFieldValuesGeneratorTest extends MockeryTestCase
{
    public const PERIOD_NAME_KEY = 'period.name.key';

    private $irhpPermitApplication;

    private $irhpPermitStock;

    private $currentFieldValuesGenerator;

    public function setUp(): void
    {
        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $this->irhpPermitStock = m::mock(IrhpPermitStock::class);
        $this->irhpPermitStock->shouldReceive('getPeriodNameKey')
            ->withNoArgs()
            ->andReturn(self::PERIOD_NAME_KEY);

        $this->currentFieldValuesGenerator = new CurrentFieldValuesGenerator();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGenerate')]
    public function testGenerate(mixed $bilateralRequired, mixed $permitUsageSelection, mixed $expected): void
    {
        $this->irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($this->irhpPermitStock);
        $this->irhpPermitApplication->shouldReceive('getBilateralRequired')
            ->withNoArgs()
            ->andReturn($bilateralRequired);
        $this->irhpPermitApplication->shouldReceive('getBilateralPermitUsageSelection')
            ->withNoArgs()
            ->andReturn($permitUsageSelection);

        $this->assertEquals(
            $expected,
            $this->currentFieldValuesGenerator->generate($this->irhpPermitStock, $this->irhpPermitApplication)
        );
    }

    public static function dpGenerate(): array
    {
        return [
            [
                [
                    IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => 10,
                    IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => 15
                ],
                RefData::JOURNEY_SINGLE,
                [
                    RefData::JOURNEY_SINGLE => [
                        IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => 10,
                        IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => 15
                    ],
                    RefData::JOURNEY_MULTIPLE => [
                        IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => null,
                        IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => null
                    ]
                ]
            ],
            [
                [
                    IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => 20,
                    IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => 25
                ],
                RefData::JOURNEY_MULTIPLE,
                [
                    RefData::JOURNEY_SINGLE => [
                        IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => null,
                        IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => null
                    ],
                    RefData::JOURNEY_MULTIPLE => [
                        IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => 20,
                        IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => 25
                    ]
                ]
            ]
        ];
    }

    public function testGenerateNoIrhpPermitApplication(): void
    {
        $expected = [
            RefData::JOURNEY_SINGLE => [
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => null,
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => null
            ],
            RefData::JOURNEY_MULTIPLE => [
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => null,
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => null
            ]
        ];

        $this->assertEquals(
            $expected,
            $this->currentFieldValuesGenerator->generate($this->irhpPermitStock, null)
        );
    }

    public function testGenerateNonMatchingIrhpPermitApplication(): void
    {
        $this->irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn(m::mock(IrhpPermitStock::class));

        $expected = [
            RefData::JOURNEY_SINGLE => [
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => null,
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => null
            ],
            RefData::JOURNEY_MULTIPLE => [
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => null,
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => null
            ]
        ];

        $this->assertEquals(
            $expected,
            $this->currentFieldValuesGenerator->generate($this->irhpPermitStock, $this->irhpPermitApplication)
        );
    }
}
