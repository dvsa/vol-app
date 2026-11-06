<?php

namespace Common\FormService\Form\Lva;

use Common\FeatureToggle;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;

/**
 * The contact SMS number only shows once the sms_number_capture toggle is on. Until then the
 * field is removed, so nobody is asked for a number we can't use yet and a save leaves any
 * existing number alone.
 *
 * @property FormHelperService $formHelper
 */
trait SmsNumberRequiredTrait
{
    /**
     * Remove the SMS number unless capture is on. Returns whether the field is still there.
     */
    protected function keepSmsNumberOnlyWithCapture(Form $form, QuerySender $querySender): bool
    {
        if ($querySender->featuresEnabled([FeatureToggle::SMS_NUMBER_CAPTURE])) {
            return true;
        }

        $this->formHelper->removeFieldList($form, 'contact', ['phone_sms', 'phone_sms_id', 'phone_sms_version']);

        return false;
    }

    /**
     * Selfserve: with capture on the SMS number is required, otherwise it isn't shown.
     */
    protected function requireSmsNumber(Form $form, QuerySender $querySender): void
    {
        if (!$this->keepSmsNumberOnlyWithCapture($form, $querySender)) {
            return;
        }

        $form->getInputFilter()->get('contact')->get('phone_sms')->setRequired(true);
        $form->get('contact')->get('phone_sms')->setLabel('contact-number-sms');
    }
}
