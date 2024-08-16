<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

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
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":18})
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
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\EmailAddress")
     */
    public $emailAddress = null;
}
