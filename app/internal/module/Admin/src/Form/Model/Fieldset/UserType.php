<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"id":"userType"})
 * @Form\Type("Zend\Form\Fieldset")
 * @Form\Name("user-type")
 */
class UserType
{
    /**
     * @Form\Options({
     *     "label": "Type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\UserTypesListDataService",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(true)
     * @Form\Attributes({"id":"userType","placeholder":"", "required":false})
     */
    public $userType = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"team","placeholder":"", "required":false})
     * @Form\Options({
     *     "label": "Team",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\Team",
     *     "use_groups": "false"
     * })
     * @Form\Attributes({"id":"team","placeholder":"", "required":false})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DynamicSelect")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "userType",
     *          "context_values": {"internal"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "\Zend\Validator\NotEmpty"},
     *          }
     *      }
     * })
     */
    public $team = null;

    /**
     * @Form\Options({"label":"Application"})
     * @Form\Required(false)
     * @Form\Attributes({"id":"applicationTransportManagers"})
     * @Form\Type("Common\Form\Elements\Types\ApplicationTransportManagers")
     */
    public $applicationTransportManagers = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"medium","id":"transportManager","required":false})
     * @Form\Options({
     *      "label":"Transport manager",
     *      "disable_inarray_validator": false,
     *      "empty_option": "Please Select",
     *      "use_groups": "false"
     * })
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("Select")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "userType",
     *          "context_values": {"transport-manager"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "\Zend\Validator\NotEmpty"},
     *          }
     *      }
     * })
     */
    public $transportManager = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"medium","id":"localAuthority","required":false})
     * @Form\Options({
     *      "label":"Local authority",
     *      "disable_inarray_validator": false,
     *      "empty_option": "Please Select",
     *      "service_name": "Common\Service\Data\LocalAuthority",
     *      "use_groups": "false"
     * })
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DynamicSelect")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "userType",
     *          "context_values": {"local-authority"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "\Zend\Validator\NotEmpty"},
     *          }
     *      }
     * })
     */
    public $localAuthority = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"medium","id":"partnerContactDetails","required":false})
     * @Form\Options({
     *      "label":"Partner",
     *      "disable_inarray_validator": false,
     *      "empty_option": "Please Select",
     *      "service_name": "Common\Service\Data\ContactDetails",
     *      "category": "ct_partner",
     *      "use_groups": "false"
     * })
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DynamicSelect")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "userType",
     *          "context_values": {"partner"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "\Zend\Validator\NotEmpty"},
     *          }
     *      }
     * })
     */
    public $partnerContactDetails = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"medium","id":"licenceNumber","required":false})
     * @Form\Options({"label":"Licence number"})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("Text")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "userType",
     *          "context_values": {"self-service"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "\Zend\Validator\NotEmpty"},
     *          }
     *      }
     * })
     */
    public $licenceNumber = null;

    /**
     * @Form\Options({
     *     "label": "Roles",
     *     "disable_inarray_validator": false,
     *     "help-block": "Use CTRL to select multiple",
     *     "service_name": "Common\Service\Data\Role",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(true)
     * @Form\Attributes({"id":"roles","placeholder":"","class":"chosen-select-medium","required":false,
     *      "multiple":"multiple"})
     */
    public $roles = null;
}
