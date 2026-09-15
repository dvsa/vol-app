<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Retrieve a document - OTP form submit button.
 *
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"govuk-button-group"})
 */
class RetrieveOtpButtons
{
    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button"
     * })
     * @Form\Options({
     *     "label": "retrieve-document.otp.verify.label"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;
}
