<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Common\Form\QaForm;
use Common\Service\Qa\IsValidHandlerInterface;

class PermitUsageIsValidHandler implements IsValidHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isValid(QaForm $form)
    {
        $applicationStep = $form->getApplicationStep();
        $questionData = $form->getQuestionFieldsetData();

        $storedAnswer = $applicationStep['element']['value'];
        $submittedAnswer = $questionData['qaElement'];

        return (
            is_null($storedAnswer) ||
            ($storedAnswer == $submittedAnswer) ||
            ($storedAnswer != $submittedAnswer) && ($questionData['warningVisible'] == 1)
        );
    }
}
