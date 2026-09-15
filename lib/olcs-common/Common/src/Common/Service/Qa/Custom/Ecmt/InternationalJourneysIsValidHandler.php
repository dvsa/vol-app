<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Form\QaForm;
use Common\RefData;
use Common\Service\Qa\IsValidHandlerInterface;

class InternationalJourneysIsValidHandler implements IsValidHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isValid(QaForm $form)
    {
        $questionData = $form->getQuestionFieldsetData();

        return ($questionData['qaElement'] != RefData::ECMT_APP_JOURNEY_OVER_90 || $questionData['warningVisible'] != 0);
    }
}
