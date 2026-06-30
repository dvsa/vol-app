<?php

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Form\QaForm;
use Common\Service\Qa\Custom\Bilateral\PermitUsageDataHandler;
use Common\Service\Qa\Custom\Bilateral\PermitUsageIsValidHandler;
use Common\Service\Qa\Custom\Common\IsValidBasedWarningAdder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PermitUsageDataHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class PermitUsageDataHandlerTest extends MockeryTestCase
{
    public function testSetData(): void
    {
        $qaForm = m::mock(QaForm::class);

        $permitUsageIsValidHandler = m::mock(PermitUsageIsValidHandler::class);

        $isValidBasedWarningAdder = m::mock(IsValidBasedWarningAdder::class);
        $isValidBasedWarningAdder->shouldReceive('add')
            ->with($permitUsageIsValidHandler, $qaForm, 'qanda.bilaterals.permit-usage.warning')
            ->once();

        $permitUsageDataHandler = new PermitUsageDataHandler(
            $isValidBasedWarningAdder,
            $permitUsageIsValidHandler
        );

        $permitUsageDataHandler->setData($qaForm);
    }
}
