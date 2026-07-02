<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Form\QaForm;
use Common\RefData;
use Common\Service\Qa\Custom\Ecmt\InternationalJourneysIsValidHandler;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * InternationalJourneysIsValidHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class InternationalJourneysIsValidHandlerTest extends MockeryTestCase
{
    /**
     * @dataProvider dpIsValid
     */
    public function testIsValid($qaElementValue, $warningVisibleValue, $expectedIsValid): void
    {
        $questionFieldsetData = [
            'qaElement' => $qaElementValue,
            'warningVisible' => $warningVisibleValue
        ];

        $qaForm = m::mock(QaForm::class);
        $qaForm->shouldReceive('getQuestionFieldsetData')
            ->withNoArgs()
            ->andReturn($questionFieldsetData);

        $internationalJourneysIsValidHandler = new InternationalJourneysIsValidHandler();

        $this->assertEquals(
            $expectedIsValid,
            $internationalJourneysIsValidHandler->isValid($qaForm)
        );
    }

    /**
     * @return (bool|int|string)[][]
     *
     * @psalm-return list{list{'inter_journey_less_60', 0, true}, list{'inter_journey_60_90', 0, true}, list{'inter_journey_more_90', 0, false}, list{'inter_journey_less_60', 1, true}, list{'inter_journey_60_90', 1, true}, list{'inter_journey_more_90', 1, true}}
     */
    public function dpIsValid(): array
    {
        return [
            [RefData::ECMT_APP_JOURNEY_LESS_60, 0, true],
            [RefData::ECMT_APP_JOURNEY_60_90, 0, true],
            [RefData::ECMT_APP_JOURNEY_OVER_90, 0, false],
            [RefData::ECMT_APP_JOURNEY_LESS_60, 1, true],
            [RefData::ECMT_APP_JOURNEY_60_90, 1, true],
            [RefData::ECMT_APP_JOURNEY_OVER_90, 1, true],
        ];
    }
}
