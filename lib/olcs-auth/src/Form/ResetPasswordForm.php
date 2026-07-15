<?php

namespace Dvsa\Olcs\Auth\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("reset-password-form")
 * @Form\Attributes({"method":"post"})
 */
class ResetPasswordForm
{
    /**
     * @Form\Options({
     *     "label": "auth.reset-password.new-password",
     *     "short-label": "auth.reset-password.new-password",
     *     "shouldEscapeMessages": false,
     * })
     * @Form\Attributes({"id": "auth.reset-password.new-password"})
     * @Form\Filter({"name": "Laminas\Filter\StringTrim"})
     * @Form\Validator({"name":"Laminas\Validator\StringLength","options":{"min":12}})
     * @Form\Validator({
     *     "name":"Laminas\Validator\Regex",
     *     "options":{
     *         "pattern":"/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{12,}$/",
     *         "message": "auth.expired-password.failed.reason.New password does not meet the password policy requirements."
     *     }
     * })
     * @Form\Type("Password")
     */
    public $newPassword;

    /**
     * @Form\Options({
     *     "label": "auth.reset-password.confirm-password",
     *     "short-label": "auth.reset-password.confirm-password"
     * })
     * @Form\Attributes({"id": "auth.reset-password.confirm-password"})
     * @Form\Filter({"name": "Laminas\Filter\StringTrim"})
     * @Form\Validator({"name": "Laminas\Validator\Identical", "options": {"token": "newPassword"}})
     * @Form\Validator({"name":"Laminas\Validator\StringLength","options":{"min":12}})
     * @Form\Type("Password")
     */
    public $confirmPassword;

    /**
     * @Form\Attributes({
     *     "id": "auth.reset-password.button",
     *     "value": "auth.reset-password.button",
     *     "class": "govuk-button",
     * })
     * @Form\Type("Submit")
     */
    public $submit;
}
