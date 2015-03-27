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
     * @Form\Attributes({"id":"hintQuestion1","placeholder":"", "required":false})
     * @Form\Options({
     *     "label": "Hint question 1",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\HintQuestion",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
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
     * @Form\Attributes({"id":"hintQuestion2","placeholder":"", "required":false})
     * @Form\Options({
     *     "label": "Hint question 2",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\HintQuestion",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
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
     * @Form\Options({"checked_value":"1","unchecked_value":"0","label":"Reset password at next login"})
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
     * @Form\Attributes({"id":"Attempts", "required": false})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $attempts = null;

    /**
     * @Form\Options({"label":"Reset password expiry"})
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
     * @Form\Options({"label":"Account disabled"})
     * @Form\Required(false)
     * @Form\Attributes({"id":"accountDisabled", "required": false})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $accountDisabled = null;
}
