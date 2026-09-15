<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("fee-payment-actions")
 * @Form\Attributes({"class":"govuk-button-group"})
 */
class FeePaymentActions
{
    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     *     "id":"submitAndPay",
     * })
     * @Form\Options({"label": "continuation.payment.pay-and-submit"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $pay;
}
