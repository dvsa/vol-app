<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Type("Zend\Form\Fieldset")
 * @Form\Name("user-login")
 * @Form\Options({"label":"Login"})
 */
class UserLogin
{
    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"username","placeholder":"","class":"medium"})
     * @Form\Options({"label":"Username"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":40}})
     */
    public $loginId = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"email_address","placeholder":"","class":"medium"})
     * @Form\Options({"label":"Email address"})
     * @Form\Type("Email")
     */
    public $emailAddress = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"confirm_email_address","placeholder":"","class":"medium"})
     * @Form\Options({"label":"Confirm email address"})
     * @Form\Type("Email")
     */
    public $confirmEmailAddress = null;
}
