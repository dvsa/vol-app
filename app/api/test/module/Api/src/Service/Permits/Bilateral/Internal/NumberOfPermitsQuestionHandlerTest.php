<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\NoOfPermitsConditionalUpdater;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\BilateralRequiredGenerator;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\NumberOfPermitsQuestionHandler;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\PermitUsageSelectionGenerator;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NumberOfPermitsQuestionHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NumberOfPermitsQuestionHandlerTest extends MockeryTestCase
{
    public function testHandle(): void
    {
        $requiredPermits = [
            'requiredPermitsKey1' => 'requiredPermitsValue1',
            'requiredPermitsKey2' => 'requiredPermitsValue2'
        ];

        $permitUsageSelection = 'permitUsageSelection';

        $bilateralRequired = [
            IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => '4',
            IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => '5',
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => null
        ];

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);

        $permitUsageSelectionGenerator = m::mock(PermitUsageSelectionGenerator::class);
        $permitUsageSelectionGenerator->shouldReceive('generate')
            ->with($requiredPermits)
            ->andReturn($permitUsageSelection);

        $bilateralRequiredGenerator = m::mock(BilateralRequiredGenerator::class);
        $bilateralRequiredGenerator->shouldReceive('generate')
            ->with($requiredPermits, $permitUsageSelection)
            ->andReturn($bilateralRequired);

        $noOfPermitsConditionalUpdater = m::mock(NoOfPermitsConditionalUpdater::class);
        $noOfPermitsConditionalUpdater->shouldReceive('update')
            ->with($irhpPermitApplication, $bilateralRequired)
            ->once();

        $numberOfPermitsQuestionHandler = new NumberOfPermitsQuestionHandler(
            $permitUsageSelectionGenerator,
            $bilateralRequiredGenerator,
            $noOfPermitsConditionalUpdater
        );

        $numberOfPermitsQuestionHandler->handle($qaContext, $requiredPermits);
    }
}
