<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Common;

use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\ModifiedAnswerUpdater;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\StandardAndCabotageUpdater;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StandardAndCabotageUpdaterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StandardAndCabotageUpdaterTest extends MockeryTestCase
{
    public function testUpdate(): void
    {
        $oldCabotage = 'old_cabotage_selection';
        $newCabotage = 'new_cabotage_selection';

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity->getBilateralCabotageSelection')
            ->withNoArgs()
            ->andReturn($oldCabotage);

        $modifiedAnswerUpdater = m::mock(ModifiedAnswerUpdater::class);
        $modifiedAnswerUpdater->shouldReceive('update')
            ->with($qaContext, $oldCabotage, $newCabotage)
            ->once();

        $permitUsageUpdater = new StandardAndCabotageUpdater($modifiedAnswerUpdater);
        $permitUsageUpdater->update($qaContext, $newCabotage);
    }
}
