<?php

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\StandardAndCabotageUpdater;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\BilateralRequiredGenerator;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\PermitUsageSelectionGenerator;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\StandardAndCabotageQuestionHandler;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StandardAndCabotageQuestionHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StandardAndCabotageQuestionHandlerTest extends MockeryTestCase
{
    /**
     * @dataProvider dpHandle
     */
    public function testHandle($bilateralRequired, $expectedAnswer)
    {
        $permitUsageSelection = 'permit_usage_selection';

        $requiredPermits = [
            'requiredPermitsKey1' => 'requiredPermitsValue1',
            'requiredPermitsKey2' => 'requiredPermitsValue2',
            'requiredPermitsKey3' => 'requiredPermitsValue3',
        ];

        $qaContext = m::mock(QaContext::class);

        $permitUsageSelectionGenerator = m::mock(PermitUsageSelectionGenerator::class);
        $permitUsageSelectionGenerator->shouldReceive('generate')
            ->with($requiredPermits)
            ->andReturn($permitUsageSelection);

        $bilateralRequiredGenerator = m::mock(BilateralRequiredGenerator::class);
        $bilateralRequiredGenerator->shouldReceive('generate')
            ->with($requiredPermits, $permitUsageSelection)
            ->andReturn($bilateralRequired);

        $standardAndCabotageUpdater = m::mock(StandardAndCabotageUpdater::class);
        $standardAndCabotageUpdater->shouldReceive('update')
            ->with($qaContext, $expectedAnswer)
            ->once();

        $standardAndCabotageQuestionHandler = new StandardAndCabotageQuestionHandler(
            $permitUsageSelectionGenerator,
            $bilateralRequiredGenerator,
            $standardAndCabotageUpdater
        );

        $standardAndCabotageQuestionHandler->handle($qaContext, $requiredPermits);
    }

    public function dpHandle()
    {
        return [
            'standard only' => [
                [
                    IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => 3,
                    IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => null
                ],
                Answer::BILATERAL_STANDARD_ONLY
            ],
            'cabotage only' => [
                [
                    IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => null,
                    IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => 7
                ],
                Answer::BILATERAL_CABOTAGE_ONLY
            ],
            'standard and cabotage' => [
                [
                    IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => 4,
                    IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => 7
                ],
                Answer::BILATERAL_STANDARD_AND_CABOTAGE
            ]
        ];
    }
}
