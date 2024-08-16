<?php

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsAnswerSummaryProvider;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsAnswerSummaryProviderTest
 */
class NoOfPermitsAnswerSummaryProviderTest extends MockeryTestCase
{
    private $noOfPermitsAnswerSummaryProvider;

    public function setUp(): void
    {
        $this->noOfPermitsAnswerSummaryProvider = new NoOfPermitsAnswerSummaryProvider();
    }

    public function testGetTemplateName()
    {
        $this->assertEquals(
            'bilateral-permits-required',
            $this->noOfPermitsAnswerSummaryProvider->getTemplateName()
        );
    }

    public function testShouldIncludeSlug()
    {
        $qaEntity = m::mock(QaEntityInterface::class);

        $this->assertTrue(
            $this->noOfPermitsAnswerSummaryProvider->shouldIncludeSlug($qaEntity)
        );
    }

    /**
     * @dataProvider dpGetTemplateVariables
     */
    public function testGetTemplateVariables($bilateralPermitUsageSelection, $bilateralRequired, $expectedTemplateVariables)
    {
        $isSnapshot = false;

        $irhpPermitApplicationEntity = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplicationEntity->shouldReceive('getFilteredBilateralRequired')
            ->withNoArgs()
            ->andReturn($bilateralRequired)
            ->shouldReceive('getBilateralPermitUsageSelection')
            ->withNoArgs()
            ->andReturn($bilateralPermitUsageSelection);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpPermitApplicationEntity);

        $element = m::mock(ElementInterface::class);

        $templateVariables = $this->noOfPermitsAnswerSummaryProvider->getTemplateVariables(
            $qaContext,
            $element,
            $isSnapshot
        );

        $this->assertEquals($expectedTemplateVariables, $templateVariables);
    }

    public function dpGetTemplateVariables()
    {
        $requiredStandard = 5;
        $requiredCabotage = 7;

        return [
            [
                RefData::JOURNEY_MULTIPLE,
                [
                    IrhpPermitApplicationEntity::BILATERAL_STANDARD_REQUIRED => $requiredStandard,
                    IrhpPermitApplicationEntity::BILATERAL_CABOTAGE_REQUIRED => $requiredCabotage,
                ],
                [
                    'rows' => [
                        [
                            'key' => 'qanda.bilateral.no-of-permits.journey_multiple.standard',
                            'count' => $requiredStandard,
                        ],
                        [
                            'key' => 'qanda.bilateral.no-of-permits.journey_multiple.cabotage',
                            'count' => $requiredCabotage,
                        ],
                    ],
                ],
            ],
            [
                RefData::JOURNEY_MULTIPLE,
                [
                    IrhpPermitApplicationEntity::BILATERAL_CABOTAGE_REQUIRED => $requiredCabotage,
                ],
                [
                    'rows' => [
                        [
                            'key' => 'qanda.bilateral.no-of-permits.journey_multiple.cabotage',
                            'count' => $requiredCabotage,
                        ],
                    ],
                ],
            ],
            [
                RefData::JOURNEY_MULTIPLE,
                [
                    IrhpPermitApplicationEntity::BILATERAL_STANDARD_REQUIRED => $requiredStandard,
                ],
                [
                    'rows' => [
                        [
                            'key' => 'qanda.bilateral.no-of-permits.journey_multiple.standard',
                            'count' => $requiredStandard,
                        ],
                    ],
                ],
            ],
            [
                RefData::JOURNEY_SINGLE,
                [
                    IrhpPermitApplicationEntity::BILATERAL_STANDARD_REQUIRED => $requiredStandard,
                    IrhpPermitApplicationEntity::BILATERAL_CABOTAGE_REQUIRED => $requiredCabotage,
                ],
                [
                    'rows' => [
                        [
                            'key' => 'qanda.bilateral.no-of-permits.journey_single.standard',
                            'count' => $requiredStandard,
                        ],
                        [
                            'key' => 'qanda.bilateral.no-of-permits.journey_single.cabotage',
                            'count' => $requiredCabotage,
                        ],
                    ],
                ],
            ],
            [
                RefData::JOURNEY_SINGLE,
                [
                    IrhpPermitApplicationEntity::BILATERAL_CABOTAGE_REQUIRED => $requiredCabotage,
                ],
                [
                    'rows' => [
                        [
                            'key' => 'qanda.bilateral.no-of-permits.journey_single.cabotage',
                            'count' => $requiredCabotage,
                        ],
                    ],
                ],
            ],
            [
                RefData::JOURNEY_SINGLE,
                [
                    IrhpPermitApplicationEntity::BILATERAL_STANDARD_REQUIRED => $requiredStandard,
                ],
                [
                    'rows' => [
                        [
                            'key' => 'qanda.bilateral.no-of-permits.journey_single.standard',
                            'count' => $requiredStandard,
                        ],
                    ],
                ],
            ],
        ];
    }
}
