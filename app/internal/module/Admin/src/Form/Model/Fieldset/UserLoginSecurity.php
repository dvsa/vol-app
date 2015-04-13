<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Type("Zend\Form\Fieldset")
 * @Form\Name("user-login-security")
 * @Form\Options({"label":"Login"})
 */
class UserLoginSecurity
{
    /**
     * @Form\Options({"label":"Username"})
     * @Form\Required(true)
     * @Form\Attributes({"id":"username","placeholder":"","class":"medium", "required":false})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":40}})
     */
    public $loginId = null;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Required(false)
     * @Form\Attributes({"id":"memorableWord","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Memorable word"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":10}})
     */
    public $memorableWord = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Reset password at next login"})
     * @Form\Type("OlcsCheckbox")
     */
    public $mustResetPassword;

    /**
     * @Form\Options({"label":"Last successful login"})
     * @Form\Required(false)
     * @Form\Attributes({"id":"lastSuccessfulLogin", "required": false})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $lastSuccessfulLogin = null;

    /**
     * @Form\Options({"label":"Attempts"})
     * @Form\Required(false)
     * @Form\Attributes({"id":"attempts", "required": false})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $attempts = null;

    /**
     * @Form\Options({"label":"Reset password expiry"})
     * @Form\Required(false)
     * @Form\Attributes({"id":"resetPasswordExpiryDate", "required": false})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $resetPasswordExpiryDate = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Disable account"})
     * @Form\Type("OlcsCheckbox")
     */
    public $accountDisabled = null;

    /**
     * @Form\Options({"label":"Account disabled"})
     * @Form\Required(false)
     * @Form\Attributes({"id":"lockedDate", "required": false})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $lockedDate = null;
}
