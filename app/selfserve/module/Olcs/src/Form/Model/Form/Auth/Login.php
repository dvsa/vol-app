<?php

namespace Olcs\Form\Model\Form\Auth;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("login-form")
 * @Form\Attributes({"method":"post"})
 */
class Login
{
    /**
     * @Form\Options({
     *     "label": "auth.login.username",
     *     "short-label": "auth.login.username",
     *     "label_attributes": {
     *         "aria-label": "Enter your username"
     *     }
     * })
     * @Form\Attributes({
     *     "id": "auth.login.username"
     * })
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Filter("Common\Filter\StripSpaces")
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\Username")
     * @Form\Type("Text")
     */
    public $username = null;

    /**
     * @Form\Options({
     *     "label": "auth.login.password",
     *     "short-label": "auth.login.password",
     *     "label_attributes": {
     *         "aria-label": "Enter your password"
     *     }
     * })
     * @Form\Attributes({
     *     "id": "auth.login.password"
     * })
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":8})
     * @Form\Type("Password")
     */
    public $password = null;

    /**
     * @Form\Options({
     *     "label": "auth.login.button",
     * })
     * @Form\Attributes({
     *     "id": "auth.login.button",
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button"
     * })
     * @Form\Type("Button")
     */
    public $submit = null;
}
