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
     *     "label_attributes": {
     *         "aria-label": "Enter your licence number"
     *     }
     * })
     * @Form\Attributes({"class":"medium"})
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":18}})
     */
    public $licenceNumber = null;

    /**
     * @Form\Type("Text")
     * @Form\Options({
     *     "label":"user-forgot-username.field.emailAddress.label",
     *     "label_attributes": {
     *         "aria-label": "Enter your email address"
     *     }
     * })
     * @Form\Attributes({"class":"long"})
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\EmailAddress"})
     */
    public $emailAddress = null;
}
