<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Form\QaForm;
use Common\Service\Qa\IsValidHandlerInterface;

class AnnualTripsAbroadIsValidHandler implements IsValidHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isValid(QaForm $form)
    {
        $applicationStep = $form->getApplicationStep();
        $questionData = $form->getQuestionFieldsetData();

        $intensityWarningThreshold = $applicationStep['element']['intensityWarningThreshold'];
        $permitsRequired = (int) $questionData['qaElement'];

        return ($permitsRequired <= $intensityWarningThreshold || $questionData['warningVisible'] != 0);
    }
}
