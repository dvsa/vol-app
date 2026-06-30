<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"govuk-button-group"})
 */
class DeclarationFormActions
{
    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     *     "id":"sign",
     * })
     * @Form\Options({"label": "application.review-declarations.sign-button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $sign;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     *     "id":"submitAndPay",
     * })
     * @Form\Options({"label": "continue-to-payment.button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submitAndPay;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     *     "id": "submit"
     * })
     * @Form\Options({"label": "continue.button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit;
}
