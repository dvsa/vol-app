<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("UserForgotUsername")
 * @Form\Attributes({"method":"post","label":"user-forgot-username.form.label"})
 * @Form\Options({"prefer_form_input_filter": true, "label": "user-forgot-username.form.label"})
 */
class UserForgotUsername
{
    /**
     * @Form\Type("Text")
     * @Form\Options({
     *     "label": "user-forgot-username.field.licenceNumber.label",
     * })
     * @Form\Attributes({"class":"medium"})
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":35}})
     */
    public $licenceNumber = null;

    /**
     * @Form\Type("Text")
     * @Form\Options({
     *     "label":"user-forgot-username.field.emailAddress.label"
     * })
     * @Form\Attributes({"class":"medium"})
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\EmailAddress"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":60}})
     */
    public $emailAddress = null;
}
