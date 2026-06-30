<?php

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Form\QaForm;
use Common\Service\Qa\Custom\Bilateral\StandardAndCabotageFieldsetPopulator;
use Common\Service\Qa\Custom\Bilateral\StandardAndCabotageIsValidHandler;
use Common\Service\Qa\Custom\Bilateral\StandardAndCabotageSubmittedAnswerGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StandardAndCabotageIsValidHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StandardAndCabotageIsValidHandlerTest extends MockeryTestCase
{
    /**
     * @dataProvider dpIsValid
     */
    public function testIsValid($storedAnswerValue, $submittedAnswerValue, $warningVisibleValue, $expectedIsValid): void
    {
        $questionFieldsetData = [
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

        $standardAndCabotageSubmittedAnswerGenerator = m::mock(StandardAndCabotageSubmittedAnswerGenerator::class);
        $standardAndCabotageSubmittedAnswerGenerator->shouldReceive('generate')
            ->with($qaForm)
            ->andReturn($submittedAnswerValue);

        $standardAndCabotageIsValidHandler = new StandardAndCabotageIsValidHandler(
            $standardAndCabotageSubmittedAnswerGenerator
        );

        $this->assertEquals(
            $expectedIsValid,
            $standardAndCabotageIsValidHandler->isValid($qaForm)
        );
    }


    /**
     * @return (bool|null|string)[][]
     *
     * @psalm-return array{'stored answer is null, valid answer selected': list{null, 'qanda.bilaterals.cabotage.answer.cabotage-only', 'none', true}, 'submitted answer is invalid': list{'qanda.bilaterals.cabotage.answer.standard-and-cabotage', '', 'none', true}, 'stored answer equals submitted answer, warning not visible': list{'qanda.bilaterals.cabotage.answer.standard-and-cabotage', 'qanda.bilaterals.cabotage.answer.standard-and-cabotage', 'none', true}, 'stored answer equals submitted answer, warning visible': list{'qanda.bilaterals.cabotage.answer.standard-and-cabotage', 'qanda.bilaterals.cabotage.answer.standard-and-cabotage', 'qanda.bilaterals.cabotage.answer.standard-only', true}, 'stored answer different to submitted answer, warning not visible': list{'qanda.bilaterals.cabotage.answer.standard-and-cabotage', 'qanda.bilaterals.cabotage.answer.standard-only', 'none', false}, 'stored answer different to submitted answer, warning visible': list{'qanda.bilaterals.cabotage.answer.standard-and-cabotage', 'qanda.bilaterals.cabotage.answer.standard-only', 'qanda.bilaterals.cabotage.answer.standard-only', true}}
     */
    public function dpIsValid(): array
    {
        return [
            'stored answer is null, valid answer selected' => [
                null,
                StandardAndCabotageFieldsetPopulator::ANSWER_CABOTAGE_ONLY,
                'none',
                true
            ],
            'submitted answer is invalid' => [
                StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE,
                '',
                'none',
                true
            ],
            'stored answer equals submitted answer, warning not visible' => [
                StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE,
                StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE,
                'none',
                true
            ],
            'stored answer equals submitted answer, warning visible' => [
                StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE,
                StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE,
                StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_ONLY,
                true
            ],
            'stored answer different to submitted answer, warning not visible' => [
                StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE,
                StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_ONLY,
                'none',
                false
            ],
            'stored answer different to submitted answer, warning visible' => [
                StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE,
                StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_ONLY,
                StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_ONLY,
                true
            ],
        ];
    }
}
