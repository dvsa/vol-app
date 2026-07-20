<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Retrieve a document - OTP code field (6 numeric digits, required).
 *
 * @Form\Name("RetrieveOtp")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class RetrieveOtp
{
    /**
     * @Form\Type("Text")
     * @Form\Options({
     *     "label": "retrieve-document.otp.code.label",
     *     "hint": "retrieve-document.otp.code.hint",
     *     "label_attributes": {
     *         "aria-label": "Enter the 6 digit security code"
     *     }
     * })
     * @Form\Attributes({
     *     "class": "govuk-input govuk-input--width-5",
     *     "inputmode": "numeric",
     *     "autocomplete": "one-time-code",
     *     "pattern": "[0-9]*",
     *     "maxlength": 6,
     *     "id": "code"
     * })
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"messages":{"isEmpty":"retrieve-document.otp.code.error.required"}})
     * @Form\Validator("Laminas\Validator\Digits", options={"messages":{"notDigits":"retrieve-document.otp.code.error.format","stringEmpty":"retrieve-document.otp.code.error.required"}})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":6,"max":6,"messages":{"stringLengthTooShort":"retrieve-document.otp.code.error.format","stringLengthTooLong":"retrieve-document.otp.code.error.format"}})
     */
    public $code = null;
}
