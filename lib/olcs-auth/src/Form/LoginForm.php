<?php

namespace Dvsa\Olcs\Auth\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("login-form")
 * @Form\Attributes({"method":"post"})
 */
class LoginForm
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
     * @Form\Filter({"name": "Laminas\Filter\StringTrim"})
     * @Form\Filter({"name": "Common\Filter\StripSpaces"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\Username"})
     * @Form\Type("Text")
     */
    public $username;

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
     * @Form\Filter({"name": "Laminas\Filter\StringTrim"})
     * @Form\Type("Password")
     */
    public $password;

    /**
     * @Form\Attributes({
     *     "id": "auth.login.button",
     *     "value": "auth.login.button",
     *     "class": "govuk-button",
     * })
     * @Form\Type("Submit")
     */
    public $submit;
}
