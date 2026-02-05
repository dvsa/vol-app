<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\PermitUsageUpdater;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\PermitUsageQuestionHandler;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\PermitUsageSelectionGenerator;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PermitUsageQuestionHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class PermitUsageQuestionHandlerTest extends MockeryTestCase
{
    public function testHandle(): void
    {
        $requiredPermits = [
            'requiredPermitsKey1' => 'requiredPermitsValue1',
            'requiredPermitsKey2' => 'requiredPermitsValue2'
        ];

        $permitUsageSelection = 'permitUsageSelection';

        $qaContext = m::mock(QaContext::class);

        $permitUsageSelectionGenerator = m::mock(PermitUsageSelectionGenerator::class);
        $permitUsageSelectionGenerator->shouldReceive('generate')
            ->with($requiredPermits)
            ->andReturn($permitUsageSelection);

        $permitUsageUpdater = m::mock(PermitUsageUpdater::class);
        $permitUsageUpdater->shouldReceive('update')
            ->with($qaContext, $permitUsageSelection)
            ->once();

        $permitUsageQuestionHandler = new PermitUsageQuestionHandler(
            $permitUsageSelectionGenerator,
            $permitUsageUpdater
        );

        $permitUsageQuestionHandler->handle($qaContext, $requiredPermits);
    }
}
