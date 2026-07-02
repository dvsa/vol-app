<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Form\QaForm;
use Common\Service\Qa\Custom\Common\IsValidBasedWarningAdder;
use Common\Service\Qa\Custom\Ecmt\AnnualTripsAbroadDataHandler;
use Common\Service\Qa\Custom\Ecmt\AnnualTripsAbroadIsValidHandler;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * AnnualTripsAbroadDataHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AnnualTripsAbroadDataHandlerTest extends MockeryTestCase
{
    public function testSetData(): void
    {
        $qaForm = m::mock(QaForm::class);

        $annualTripsAbroadIsValidHandler = m::mock(AnnualTripsAbroadIsValidHandler::class);

        $isValidBasedWarningAdder = m::mock(IsValidBasedWarningAdder::class);
        $isValidBasedWarningAdder->shouldReceive('add')
            ->with($annualTripsAbroadIsValidHandler, $qaForm, 'permits.form.trips.warning')
            ->once();

        $annualTripsAbroadDataHandler = new AnnualTripsAbroadDataHandler(
            $isValidBasedWarningAdder,
            $annualTripsAbroadIsValidHandler
        );

        $annualTripsAbroadDataHandler->setData($qaForm);
    }
}
