<?php

declare(strict_types=1);

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
final class StandardAndCabotageIsValidHandlerTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValid')]
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
     * @return \Iterator<(int | string), array<(bool | string | null)>>
     *
     * @psalm-return array{'stored answer is null, valid answer selected': list{null, 'qanda.bilaterals.cabotage.answer.cabotage-only', 'none', true}, 'submitted answer is invalid': list{'qanda.bilaterals.cabotage.answer.standard-and-cabotage', '', 'none', true}, 'stored answer equals submitted answer, warning not visible': list{'qanda.bilaterals.cabotage.answer.standard-and-cabotage', 'qanda.bilaterals.cabotage.answer.standard-and-cabotage', 'none', true}, 'stored answer equals submitted answer, warning visible': list{'qanda.bilaterals.cabotage.answer.standard-and-cabotage', 'qanda.bilaterals.cabotage.answer.standard-and-cabotage', 'qanda.bilaterals.cabotage.answer.standard-only', true}, 'stored answer different to submitted answer, warning not visible': list{'qanda.bilaterals.cabotage.answer.standard-and-cabotage', 'qanda.bilaterals.cabotage.answer.standard-only', 'none', false}, 'stored answer different to submitted answer, warning visible': list{'qanda.bilaterals.cabotage.answer.standard-and-cabotage', 'qanda.bilaterals.cabotage.answer.standard-only', 'qanda.bilaterals.cabotage.answer.standard-only', true}}
     */
    public static function dpIsValid(): \Iterator
    {
        yield 'stored answer is null, valid answer selected' => [
            null,
            StandardAndCabotageFieldsetPopulator::ANSWER_CABOTAGE_ONLY,
            'none',
            true
        ];
        yield 'submitted answer is invalid' => [
            StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE,
            '',
            'none',
            true
        ];
        yield 'stored answer equals submitted answer, warning not visible' => [
            StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE,
            StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE,
            'none',
            true
        ];
        yield 'stored answer equals submitted answer, warning visible' => [
            StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE,
            StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE,
            StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_ONLY,
            true
        ];
        yield 'stored answer different to submitted answer, warning not visible' => [
            StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE,
            StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_ONLY,
            'none',
            false
        ];
        yield 'stored answer different to submitted answer, warning visible' => [
            StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE,
            StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_ONLY,
            StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_ONLY,
            true
        ];
    }
}
