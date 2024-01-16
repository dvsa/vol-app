<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"id":"userType"})
 * @Form\Type("Laminas\Form\Fieldset")
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
     *     "service_name": "Olcs\Service\Data\Team",
     *     "use_groups": "false"
     * })
     * @Form\Attributes({"id":"team","placeholder":"", "required":false})
     * @Form\Type("DynamicSelect")
     * @Form\Validator("NotEmpty", options={"null"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "userType",
     *          "context_values": {"internal"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "\Laminas\Validator\NotEmpty"},
     *          }
     *      }
     * })
     */
    public $team = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"currentTransportManagerHtml", "required": false, "data-container-class":"tm-current"})
     * @Form\Options({
     *     "label": "Current Transport Manager"
     * })
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $currentTransportManagerHtml = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $currentTransportManager = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $currentTransportManagerName = null;

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
     * @Form\Type("Select")
     * @Form\Validator({"name":"Laminas\Validator\NotEmpty","options":{"null"}})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "userType",
     *          "context_values": {"transport-manager"},
     *          "allow_empty": false,
     *          "validators": {
     *              {
     *                  "name": "ValidateIf",
     *                  "options": {
     *                      "context_field": "currentTransportManager",
     *                      "context_values": {""},
     *                      "allow_empty": false,
     *                      "validators": {
     *                          {"name": "\Laminas\Validator\NotEmpty"},
     *                      }
     *                  }
     *              }
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
     * @Form\Type("DynamicSelect")
     * @Form\Validator({"name":"Laminas\Validator\NotEmpty","options":{"null"}})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "userType",
     *          "context_values": {"local-authority"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "\Laminas\Validator\NotEmpty"},
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
     *      "context": Common\RefData::CONTACT_TYPE_PARTNER,
     *      "use_groups": false,
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Validator({"name":"Laminas\Validator\NotEmpty","options":{"null"}})
     * @Form\Validator({
     *     "name": "ValidateIf",
     *     "options":{
     *          "context_field": "userType",
     *          "context_values": {"partner"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "\Laminas\Validator\NotEmpty"},
     *          }
     *      }
     * })
     */
    public $partnerContactDetails = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"medium","id":"licenceNumber","required":false})
     * @Form\Options({"label":"Licence number"})
     * @Form\Type("Text")
     * @Form\Validator({"name":"Laminas\Validator\NotEmpty","options":{"null"}})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "userType",
     *          "context_values": {"operator"},
     *          "allow_empty": false,
     *          "validators": {
     *              {
     *                  "name": "ValidateIf",
     *                  "options": {
     *                      "context_field": "id",
     *                      "context_values": {""},
     *                      "allow_empty": false,
     *                      "validators": {
     *                          {"name": "\Laminas\Validator\NotEmpty"},
     *                      }
     *                  }
     *              }
     *          }
     *      }
     * })
     */
    public $licenceNumber = null;

    /**
     * @Form\Options({
     *     "label": "Role",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\Role",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(true)
     * @Form\Attributes({"id":"role","placeholder":"","required":false})
     */
    public $role = null;
}
