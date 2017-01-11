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
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\Username"})
     */
    public $loginId = null;

    /**
     * @Form\Options({"label":"Created on"})
     * @Form\Required(false)
     * @Form\Attributes({"id":"createdOn", "required": false})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $createdOn = null;

    /**
     * @Form\Options({"label":"Last logged in on"})
     * @Form\Required(false)
     * @Form\Attributes({"id":"lastLoginOn", "required": false})
     * @Form\Type("Common\Form\Elements\Types\HtmlDateTime")
     */
    public $lastLoggedInOn = null;

    /**
     * @Form\Options({"label":"Account locked"})
     * @Form\Required(false)
     * @Form\Attributes({"id":"locked", "required": false})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $locked = null;

    /**
     * @Form\Options({"label":"Password last reset"})
     * @Form\Required(false)
     * @Form\Attributes({"id":"passwordLastReset", "required": false})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $passwordLastReset = null;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Reset password / Unlock account",
     *      "value_options":{
     *          "":"N/A",
     *          "post":"By post",
     *          "email":"By email"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": ""})
     * @Form\Required(false)
     */
    public $resetPassword = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Disable account"})
     * @Form\Type("OlcsCheckbox")
     */
    public $accountDisabled = null;

    /**
     * @Form\Options({"label":"Account disabled"})
     * @Form\Required(false)
     * @Form\Attributes({"id":"disabledDate", "required": false})
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $disabledDate = null;
}
