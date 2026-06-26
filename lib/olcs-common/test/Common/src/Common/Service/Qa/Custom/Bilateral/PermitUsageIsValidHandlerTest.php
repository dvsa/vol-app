<?php

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Form\QaForm;
use Common\Service\Qa\Custom\Bilateral\PermitUsageIsValidHandler;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PermitUsageIsValidHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class PermitUsageIsValidHandlerTest extends MockeryTestCase
{
    /**
     * @dataProvider dpIsValid
     */
    public function testIsValid($storedAnswerValue, $qaElementValue, $warningVisibleValue, $expectedIsValid): void
    {
        $questionFieldsetData = [
            'qaElement' => $qaElementValue,
            'warningVisible' => $warningVisibleValue
        ];

        $applicationStep = [
            'element' => [
                'value' => $storedAnswerValue
            ]
        ];

        $qaForm = m::mock(QaForm::class);
        $qaForm->shouldReceive('getQuestionFieldsetData')
            ->withNoArgs()
            ->andReturn($questionFieldsetData);
        $qaForm->shouldReceive('getApplicationStep')
            ->withNoArgs()
            ->andReturn($applicationStep);

        $permitUsageIsValidHandler = new PermitUsageIsValidHandler();

        $this->assertEquals(
            $expectedIsValid,
            $permitUsageIsValidHandler->isValid($qaForm)
        );
    }

    /**
     * @return (bool|null|string)[][]
     *
     * @psalm-return list{list{null, 'journey_multiple', false, true}, list{null, 'journey_multiple', true, true}, list{'journey_multiple', 'journey_multiple', false, true}, list{'journey_multiple', 'journey_multiple', true, true}, list{'journey_single', 'journey_multiple', true, true}, list{'journey_single', 'journey_multiple', false, false}}
     */
    public function dpIsValid(): array
    {
        return [
            [null, 'journey_multiple', false, true],
            [null, 'journey_multiple', true, true],
            ['journey_multiple', 'journey_multiple', false, true],
            ['journey_multiple', 'journey_multiple', true, true],
            ['journey_single', 'journey_multiple', true, true],
            ['journey_single', 'journey_multiple', false, false],
        ];
    }
}
