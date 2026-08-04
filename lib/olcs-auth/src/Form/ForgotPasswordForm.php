<?php

namespace Dvsa\Olcs\Auth\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("forgot-password-form")
 * @Form\Attributes({"method":"post"})
 */
class ForgotPasswordForm
{
    /**
     * @Form\Options({
     *     "label": "auth.forgot-password.username",
     *     "short-label": "auth.forgot-password.username"
     * })
     * @Form\Attributes({"id": "auth.forgot-password.username"})
     * @Form\Filter({"name": "Laminas\Filter\StringTrim"})
     * @Form\Filter({"name": "Common\Filter\StripSpaces"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\Username"})
     * @Form\Type("Text")
     */
    public $username;

    /**
     * @Form\Attributes({
     *     "id": "auth.forgot-password.button",
     *     "value": "auth.forgot-password.button",
     *     "class": "govuk-button",
     * })
     * @Form\Type("Submit")
     */
    public $submit;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary",
     *     "id":"cancel",
     * })
     * @Form\Options({
     *     "label": "cancel.button",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel;
}
