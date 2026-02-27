<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\NoOfPermitsConditionalUpdater;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\NumberOfPermitsMoroccoQuestionHandler;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NumberOfPermitsMoroccoQuestionHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NumberOfPermitsMoroccoQuestionHandlerTest extends MockeryTestCase
{
    public function testHandle(): void
    {
        $moroccoPermitsRequired = '12';

        $requiredPermits = ['permitsRequired' => $moroccoPermitsRequired];

        $expectedBilateralRequired = [
            IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => null,
            IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => null,
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => $moroccoPermitsRequired
        ];

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);

        $noOfPermitsConditionalUpdater = m::mock(NoOfPermitsConditionalUpdater::class);
        $noOfPermitsConditionalUpdater->shouldReceive('update')
            ->with($irhpPermitApplication, $expectedBilateralRequired)
            ->once();

        $numberOfPermitsMoroccoQuestionHandler = new NumberOfPermitsMoroccoQuestionHandler(
            $noOfPermitsConditionalUpdater
        );

        $numberOfPermitsMoroccoQuestionHandler->handle($qaContext, $requiredPermits);
    }
}
