<?php

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

    /**
     * @dataProvider dpGenerate
     */
    public function testGenerate($bilateralRequired, $permitUsageSelection, $expected)
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

    public function dpGenerate()
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

    public function testGenerateNoIrhpPermitApplication()
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

    public function testGenerateNonMatchingIrhpPermitApplication()
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
