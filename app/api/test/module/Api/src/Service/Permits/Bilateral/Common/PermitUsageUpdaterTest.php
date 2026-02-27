<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Common;

use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\ModifiedAnswerUpdater;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\PermitUsageUpdater;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PermitUsageUpdaterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class PermitUsageUpdaterTest extends MockeryTestCase
{
    public function testUpdate(): void
    {
        $oldPermitUsage = 'old_permit_usage_selection';
        $newPermitUsage = 'new_permit_usage_selection';

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity->getBilateralPermitUsageSelection')
            ->withNoArgs()
            ->andReturn($oldPermitUsage);

        $modifiedAnswerUpdater = m::mock(ModifiedAnswerUpdater::class);
        $modifiedAnswerUpdater->shouldReceive('update')
            ->with($qaContext, $oldPermitUsage, $newPermitUsage)
            ->once();

        $permitUsageUpdater = new PermitUsageUpdater($modifiedAnswerUpdater);
        $permitUsageUpdater->update($qaContext, $newPermitUsage);
    }
}
