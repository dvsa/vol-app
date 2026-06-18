<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Common\Form\QaForm;

class StandardAndCabotageSubmittedAnswerGenerator
{
    public const PERMITTED_VALUES = [
        StandardAndCabotageFieldsetPopulator::ANSWER_CABOTAGE_ONLY,
        StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_AND_CABOTAGE,
        StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_ONLY,
    ];

    /**
     * Return the backend value of the standard and cabotage question given the form data
     *
     * @param QaForm $form
     *
     * return string
     */
    public function generate(QaForm $form)
    {
        $questionData = $form->getQuestionFieldsetData();

        $submittedAnswer = StandardAndCabotageFieldsetPopulator::ANSWER_STANDARD_ONLY;
        if ($questionData['qaElement'] == 'Y') {
            $submittedAnswer = $questionData['yesContent'];

            if (!in_array($submittedAnswer, self::PERMITTED_VALUES)) {
                return '';
            }
        }

        return $submittedAnswer;
    }
}
