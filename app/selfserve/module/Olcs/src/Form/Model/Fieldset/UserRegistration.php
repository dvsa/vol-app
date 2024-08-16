<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("UserRegistration")
 * @Form\Attributes({"method":"post","label":"user-registration.form.label"})
 * @Form\Options({"prefer_form_input_filter": true, "label": "user-registration.form.label"})
 */
class UserRegistration
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
     * @Form\Options({"label":"email-address"})
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
     * @Form\Name("isLicenceHolder")
     * @Form\Options({
     *     "label": "user-registration.field.isLicenceHolder.label",
     *     "value_options":{"N":"select-option-no", "Y":"select-option-yes"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"}
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"isLicenceHolder", "placeholder":"", "required":false})
     * @Form\Type("Radio")
     */
    public $isLicenceHolder;

    /**
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Options({
     *     "label": "user-registration.field.licenceNumber.label",
     * })
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "isLicenceHolder",
     *          "context_values": {"Y"},
     *          "validators": {
     *              {"name": "Laminas\Validator\StringLength", "options": {"min": 2, "max": 35}}
     *          }
     *      }
     * )
     */
    public $licenceNumber = null;

    /**
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"user-registration.field.organisationName.label"})
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "isLicenceHolder",
     *          "context_values": {"N"},
     *          "validators": {
     *              {"name": "Laminas\Validator\NotEmpty"}
     *          }
     *      }
     * )
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
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"string"})
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "isLicenceHolder",
     *          "context_values": {"N"},
     *          "validators": {
     *              {"name": "Laminas\Validator\NotEmpty"}
     *          }
     *      }
     * )
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
}
