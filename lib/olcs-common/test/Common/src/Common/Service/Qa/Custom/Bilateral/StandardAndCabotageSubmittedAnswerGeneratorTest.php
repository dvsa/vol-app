<?php

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Form\QaForm;
use Common\Service\Qa\Custom\Bilateral\StandardAndCabotageFieldsetPopulator;
use Common\Service\Qa\Custom\Bilateral\StandardAndCabotageSubmittedAnswerGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StandardAndCabotageSubmittedAnswerGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StandardAndCabotageSubmittedAnswerGeneratorTest extends MockeryTestCase
{
    /**
     * @dataProvider dpGenerate
     */
    public function testGenerate($questionData, $expectedSubmittedAnswer): void
    {
        $qaForm = m::mock(QaForm::class);
        $qaForm->shouldReceive('getQuestionFieldsetData')
            ->withNoArgs()
            ->andReturn($questionData);

        $standardAndCabotageSubmittedAnswerGenerator = new StandardAndCabotageSubmittedAnswerGenerator();

        $this->assertEquals(
            $expectedSubmittedAnswer,
            $standardAndCabotageSubmittedAnswerGenerator->generate($qaForm)
        );
    }

    /**
     * @return (string|string[])[][]
     *
     * @psalm-return list{list{array{qaElement: 'N', yesContent: ''}, 'qanda.bilaterals.cabotage.answer.standard-only'}, list{array{qaElement: 'Y', yesContent: 'qanda.bilaterals.cabotage.answer.standard-and-cabotage'}, 'qanda.bilaterals.cabotage.answer.standard-and-cabotage'}, list{array{qaElement: 'Y', yesContent: 'qanda.bilaterals.cabotage.answer.standard-only'}, 'qanda.bilaterals.cabotage.answer.standard-only'}, list{array{qaElement: 'Y', yesContent: ''}, ''}}
     */
    public function dpGenerate(): array
    {
        return [
            [
                [
                    'qaElement' => 'N',
                    'yesContent' => ''
                ],
                StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_ONLY
            ],
            [
                [
                    'qaElement' => 'Y',
                    'yesContent' => StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE
                ],
                StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE
            ],
            [
                [
                    'qaElement' => 'Y',
                    'yesContent' => StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_ONLY
                ],
                StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_ONLY
            ],
            [
                [
                    'qaElement' => 'Y',
                    'yesContent' => ''
                ],
                ''
            ],
        ];
    }
}
