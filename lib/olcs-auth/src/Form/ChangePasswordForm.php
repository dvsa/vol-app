<?php

namespace Dvsa\Olcs\Auth\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("change-password-form")
 * @Form\Attributes({"method":"post"})
 */
class ChangePasswordForm
{
    /**
     * @Form\Options({
     *     "label": "auth.change-password.old-password",
     *     "short-label": "auth.change-password.old-password",
     *     "error-message": "auth.change-password.old-password.error"
     * })
     * @Form\Attributes({"id": "auth.change-password.old-password"})
     * @Form\Filter({"name": "Laminas\Filter\StringTrim"})
     * @Form\Type("Password")
     */
    public $oldPassword;

    /**
     * @Form\Options({
     *     "label": "auth.change-password.new-password",
     *     "short-label": "auth.change-password.new-password"
     * })
     * @Form\Attributes({"id": "auth.change-password.new-password"})
     * @Form\Filter({"name": "Laminas\Filter\StringTrim"})
     * @Form\Validator({"name":"Laminas\Validator\StringLength","options":{"min":12, "max":160}})
     * @Form\Validator({"name":"Common\Form\Elements\Validators\PasswordConfirm","options":{"token":"confirmPassword"}})
     * @Form\Type("Password")
     */
    public $newPassword;

    /**
     * @Form\Options({
     *     "label": "auth.change-password.confirm-password",
     *     "short-label": "auth.change-password.confirm-password",
     *     "error-message": "auth.change-password.confirm-password.error"
     * })
     * @Form\Attributes({"id": "auth.change-password.confirm-password"})
     * @Form\Filter({"name": "Laminas\Filter\StringTrim"})
     * @Form\Type("Password")
     */
    public $confirmPassword;

    /**
     * @Form\Attributes({
     *     "id": "auth.change-password.button",
     *     "value": "auth.change-password.button",
     *     "class": "govuk-button"
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
