<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("UserDetails")
 * @Form\Attributes({"method":"post","label":"User details"})
 * @Form\Options({"prefer_form_input_filter": true})
 */
class UserDetails extends Base
{
    /**
     * @Form\Options({
     *     "label":"user-name",
     *     "error-message": "userDetails_loginId-error"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"username","placeholder":"","class":"medium", "required":false})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\Username"})
     */
    public $loginId = null;

    /**
     * @Form\Attributes({"id":"forename","placeholder":"","class":"medium", "required":false})
     * @Form\Options({
     *     "label":"first-name"
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":35}})
     */
    public $forename = null;

    /**
     * @Form\Attributes({"id":"familyName","placeholder":"","class":"medium", "required":false})
     * @Form\Options({"label":"last-name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":35}})
     */
    public $familyName = null;

    /**
     * @Form\Attributes({"class":"long"})
     * @Form\Options({
     *     "label":"email-address",
     *     "error-message": "userDetails_emailAddress-error"
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\EmailAddress"})
     * @Form\Validator({"name":"Common\Form\Elements\Validators\EmailConfirm","options":{"token":"emailConfirm"}})
     */
    public $emailAddress = null;

    /**
     * @Form\Attributes({"class":"long"})
     * @Form\Options({
     *     "label":"confirm-email-address",
     *     "error-message": "userDetails_emailConfirm-error"
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $emailConfirm = null;

    /**
     * @Form\Name("permission")
     * @Form\Options({
     *     "label": "manage-users.field.permission.label",
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     },
     *     "value_options":{
     *          "tm":"manage-users.field.permission.tm.label",
     *          "user":"manage-users.field.permission.user.label",
     *          "admin":"manage-users.field.permission.admin.label",
     *      },
     *      "fieldset-attributes" : {
     *          "class":"checkbox has-advanced-labels"
     *      }
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"permission", "placeholder":"", "value":"user"})
     * @Form\Type("Radio")
     */
    public $permission;

    /**
     * @Form\Attributes({"id":"translateToWelsh","placeholder":"", "data-container-class": "confirm"})
     * @Form\Options({
     *     "label": "translate-to-welsh",
     *     "checked_value":"Y",
     *     "unchecked_value":"N"
     * })
     * @Form\Type("OlcsCheckbox")
     */
    public $translateToWelsh = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $currentPermission = null;
}
