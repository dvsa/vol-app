<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("RegisterForOperator")
 * @Form\Attributes({"method":"post","label":"operator-registration.form.labe"})
 * @Form\Options({"prefer_form_input_filter": true, "label": ""})
 */
class RegisterConsultantAccount
{
    /**
     * @Form\Attributes({"value": "register-as-consultant.form.header"})
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $header = null;

    /**
     * @Form\Options({
     *     "label":"user-name",
     *     "hint": "user-registration.field.username-for-consultant.hint"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"username","placeholder":"","class":"medium", "required":false})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Filter("Laminas\Filter\StringToLower")
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\UsernameCreate")
     * @Form\Validator({"name": "Olcs\Form\Validator\UniqueConsultantDetails"})
     */
    public $loginId = null;

    /**
     * @Form\Attributes({"id":"forename","placeholder":"","class":"medium", "required":false})
     * @Form\Options({"label":"first-name"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":35})
     */
    public $forename = null;

    /**
     * @Form\Attributes({"id":"familyName","placeholder":"","class":"medium", "required":false})
     * @Form\Options({"label":"last-name"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":35})
     */
    public $familyName = null;

    /**
     * @Form\Attributes({"class":"long"})
     * @Form\Options({"label":"email-address", "hint": "user-registration.field.consultant-email.hint"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\EmailAddress")
     * @Form\Validator("Common\Form\Elements\Validators\EmailConfirm", options={"token":"emailConfirm"})
     * @Form\Validator({"name": "Olcs\Form\Validator\UniqueConsultantDetails"})
     */
    public $emailAddress = null;

    /**
     * @Form\Attributes({"class":"long"})
     * @Form\Options({"label":"confirm-email-address"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $emailConfirm = null;

    /**
     * @Form\Attributes({"id":"translateToWelsh","placeholder":""})
     * @Form\Options({
     *     "label": "translate-to-welsh",
     *     "label_attributes" : {
     *         "class":"form-control form-control--checkbox form-control--confirm"
     *     },
     *     "checked_value":"Y",
     *     "unchecked_value":"N"
     * })
     * @Form\Type("OlcsCheckbox")
     */
    public $translateToWelsh = null;

    /**
     * @Form\Attributes({"id": "termsAgreed", "placeholder": ""})
     * @Form\Options({
     *     "label": "user-registration.field.termsAgreed.label",
     *     "label_attributes" : {
     *         "class":"form-control form-control--checkbox form-control--confirm"
     *     },
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "must_be_value": "Y"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $termsAgreed = null;
}
