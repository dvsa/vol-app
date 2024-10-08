<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("RegisterOperatorAccount")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class RegisterOperatorAccount
{
    /**
     * @Form\Options({
     *     "label":"user-name",
     *     "hint": "user-registration.field.username.hint"
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
     *     "label": "register-for-operator.field.businessType.label",
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

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"}))
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OperatorRegistrationButtonGroup")
     */
    public $formActions = null;
}
