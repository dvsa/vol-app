<?php

declare(strict_types=1);

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
final class InternationalJourneysIsValidHandlerTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValid')]
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
     * @return \Iterator<(int | string), array<(bool | int | string)>>
     *
     * @psalm-return list{list{'inter_journey_less_60', 0, true}, list{'inter_journey_60_90', 0, true}, list{'inter_journey_more_90', 0, false}, list{'inter_journey_less_60', 1, true}, list{'inter_journey_60_90', 1, true}, list{'inter_journey_more_90', 1, true}}
     */
    public static function dpIsValid(): \Iterator
    {
        yield [RefData::ECMT_APP_JOURNEY_LESS_60, 0, true];
        yield [RefData::ECMT_APP_JOURNEY_60_90, 0, true];
        yield [RefData::ECMT_APP_JOURNEY_OVER_90, 0, false];
        yield [RefData::ECMT_APP_JOURNEY_LESS_60, 1, true];
        yield [RefData::ECMT_APP_JOURNEY_60_90, 1, true];
        yield [RefData::ECMT_APP_JOURNEY_OVER_90, 1, true];
    }
}
