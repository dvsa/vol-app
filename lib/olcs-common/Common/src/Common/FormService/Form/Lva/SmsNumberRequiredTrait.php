<?php

namespace Common\FormService\Form\Lva;

use Common\FeatureToggle;
use Common\Service\Cqrs\Query\QuerySender;
use Laminas\Form\Form;

/**
 * Makes the contact SMS number mandatory once the sms_number_capture toggle is on.
 * With the toggle off the field still shows, but stays optional.
 */
trait SmsNumberRequiredTrait
{
    protected function requireSmsNumber(Form $form, QuerySender $querySender): void
    {
        if (!$querySender->featuresEnabled([FeatureToggle::SMS_NUMBER_CAPTURE])) {
            return;
        }

        $form->getInputFilter()->get('contact')->get('phone_sms')->setRequired(true);
    }
}
