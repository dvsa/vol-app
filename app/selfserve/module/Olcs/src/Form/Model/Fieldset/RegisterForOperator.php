<?php

namespace Olcs\Form\Model\Fieldset;

/**
 * @Form\Name("RegisterForOperator")
 * @Form\Attributes({"method":"post","label":"operator-registration.form.labe"})
 * @Form\Options({"prefer_form_input_filter": true, "label": ""})
 */
class RegisterForOperator
{
    /**
     * @Form\Attributes({"value": "register-for-operator.form.header"})
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $header = null;

    /**
     * @Form\Options({
     *     "label":"user-name",
     *     "hint": "user-registration.field.username-for-operator.hint"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"username","placeholder":"","class":"medium", "required":false})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Filter("Laminas\Filter\StringToLower")
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\UsernameCreate")
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
     * @Form\Options({"label":"email-address", "hint": "user-registration.field.operator-email.hint"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\EmailAddress")
     * @Form\Validator("Common\Form\Elements\Validators\EmailConfirm", options={"token":"emailConfirm"})
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
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"user-registration.field.organisationName.label", "hint": "user-registration.field.organisation-name.hint"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $organisationName = null;

    /**
     * @Form\Required(true)
     * @Form\Type("DynamicRadio")
     * @Form\Options({
     *     "fieldset-attributes": {"id": "businessType"},
     *     "label": "user-registration.field.businessType.label",
     *     "label_attributes": {"class": "form-control form-control--radio"},
     *     "disable_inarray_validator": false,
     *     "category": "org_type",
     *     "exclude": {"org_t_ir"}
     * })
     */
    public $businessType = null;

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
}
