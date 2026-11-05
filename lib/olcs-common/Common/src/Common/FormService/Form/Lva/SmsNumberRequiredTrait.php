<?php

namespace Common\FormService\Form\Lva;

use Laminas\Form\Form;

/**
 * Makes the contact SMS number mandatory. Single switch point for any
 * later capture toggle.
 */
trait SmsNumberRequiredTrait
{
    protected function requireSmsNumber(Form $form): void
    {
        $form->getInputFilter()->get('contact')->get('phone_sms')->setRequired(true);
    }
}
