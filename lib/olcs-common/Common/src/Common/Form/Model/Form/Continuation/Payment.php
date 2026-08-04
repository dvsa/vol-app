<?php

namespace Common\Form\Model\Form\Continuation;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("fee-payment")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class Payment
{
    /**
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $amount;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Common\Form\Model\Form\Continuation\Fieldset\FeePaymentActions")
     */
    public $formActions;
}
