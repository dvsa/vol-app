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
     * @Form\Required(true)
     * @Form\Attributes({"id":"username","placeholder":"","class":"medium", "required":false})
     * @Form\Options({"label":"Username"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":40}})
     */
    public $loginId = null;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Required(true)
     * @Form\Attributes({"id":"memorableWord","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Memorable word"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":10}})
     */
    public $memorableWord = null;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Required(true)
     * @Form\Attributes({"id":"hintQuestion1","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Hint question 1"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":100}})
     */
    public $hintQuestion1 = null;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Required(true)
     * @Form\Attributes({"id":"hintAnswer1","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Hint answer 1"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":50}})
     */
    public $hintAnswer1 = null;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Required(true)
     * @Form\Attributes({"id":"hintQuestion2","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Hint question 2"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":100}})
     */
    public $hintQuestion2 = null;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Required(true)
     * @Form\Attributes({"id":"hintAnswer1","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Hint answer 2"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":50}})
     */
    public $hintAnswer2 = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Reset password at next login"})
     * @Form\Type("OlcsCheckbox")
     */
    public $resetPasswordAtNextLogin;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"lastSuccessfulLogin", "required": false})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $lastSuccessfulLogin = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"Attempts", "required": false})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $attempts = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"resetPasswordExpiry", "required": false})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $resetPasswordExpiry = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Disable account"})
     * @Form\Type("OlcsCheckbox")
     */
    public $disableAccount;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"accountDisabled", "required": false})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $accountDisabled = null;
}
