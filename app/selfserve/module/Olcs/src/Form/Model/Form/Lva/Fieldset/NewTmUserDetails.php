<?php

namespace Olcs\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("NewTmUserDetails")
 * @Form\Attributes({"method":"post"})
 * @Form\Options({"prefer_form_input_filter": true})
 */
class NewTmUserDetails
{
    /**
     * @Form\Attributes({"id":"forename","class":"medium"})
     * @Form\Options({
     *     "label":"tm-add-user-forename",
     *     "short-label":"tm-add-user-forename"
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":35}})
     */
    public $forename = null;

    /**
     * @Form\Attributes({"id":"familyName","class":"medium"})
     * @Form\Options({
     *     "label":"tm-add-user-familyName",
     *     "short-label":"tm-add-user-familyName"
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":35}})
     */
    public $familyName = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "tm-add-user-birthDate",
     *     "short-label":"tm-add-user-birthDate",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $birthDate = null;

    /**
     * @Form\Attributes({"id":"hasEmail"})
     * @Form\Options({
     *     "fieldset-attributes": {
     *         "class": "checkbox inline js-visible"
     *     },
     *     "label": "tm-add-user-hasEmail",
     *     "short-label":"tm-add-user-hasEmail",
     *     "value_options": {"Y": "Yes", "N": "No"},
     *     "label_attributes": {
     *         "class": "inline"
     *     }
     * })
     * @Form\Required(false)
     * @Form\Type("\Zend\Form\Element\Radio")
     */
    public $hasEmail = null;

    /**
     * @Form\Attributes({"id": "username", "class":"medium", "data-container-class": "js-visible"})
     * @Form\Options({
     *     "label":"tm-add-user-username",
     *     "short-label":"tm-add-user-username",
     *     "hint":"tm-add-user-username.hint"
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\Username"})
     * @Form\Required(false)
     */
    public $username = null;

    /**
     * @Form\Attributes({"id": "emailAddress", "class":"long", "data-container-class": "js-visible"})
     * @Form\Options({
     *     "label":"tm-add-user-email",
     *     "short-label":"tm-add-user-email"
     * })
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\EmailAddress"})
     * @Form\Validator({"name":"Common\Form\Elements\Validators\EmailConfirm","options":{"token":"emailConfirm"}})
     */
    public $emailAddress = null;

    /**
     * @Form\Attributes({"id": "emailConfirm", "class":"long", "data-container-class": "js-visible"})
     * @Form\Options({
     *     "label":"tm-add-user-confirm-email",
     *     "short-label":"tm-add-user-confirm-email"
     * })
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $emailConfirm = null;

    /**
     * @Form\Attributes({"id":"translateToWelsh","placeholder":"", "data-container-class": "confirm checkbox"})
     * @Form\Options({
     *     "label": "translate-to-welsh",
     *     "checked_value":"Y",
     *     "unchecked_value":"N"
     * })
     * @Form\Type("Common\Form\Elements\Custom\OlcsCheckbox")
     */
    public $translateToWelsh = null;

    /**
     * @Form\Attributes({
     *     "value": "markup-lva-tm-add-user-with-email-guidance",
     *     "data-container-class": "tm-guidance-email js-visible"
     * })
     * @Form\Type("\Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $emailGuidance = null;

    /**
     * @Form\Attributes({
     *     "value": "markup-lva-tm-add-user-without-email-guidance",
     *     "data-container-class": "tm-guidance-no-email"
     * })
     * @Form\Type("\Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $noEmailGuidance = null;
}
