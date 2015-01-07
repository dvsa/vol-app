<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class OppositionFields
{

    /**
     * @Form\Attributes({"id":"oppositionType","placeholder":""})
     * @Form\Options({
     *     "label": "Opposition type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select an objector type",
     *     "category": "obj_type"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $oppositionType = null;

    /**
     * @Form\Attributes({"class":"extra-long","id":"contactDetailsDescription"})
     * @Form\Options({"label":"Objector body"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":255}})
     */
    public $contactDetailsDescription = null;

    /**
     * @Form\Attributes({"id":"raisedDate"})
     * @Form\Options({
     *     "label": "Date received",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $raisedDate = null;

    /**
     * @Form\Options({
     *     "label": "Out of representation",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y",
     *     "hint": "some hint",
     *     "category": "oor",
     *     "field": "outOfRepresentationDate"
     * })
     * @Form\Required(false)
     * @Form\Type("SlaDateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     *
    public $outOfRepresentationDate = null;
*/
    /**
     * @Form\Options({
     *     "label": "Out of objection",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y",
     *     "hint": "some hint",
     *     "category": "oor",
     *     "field": "outOfObjectionDate"
     * })
     * @Form\Required(false)
     * @Form\Type("SlaDateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     *
    public $outOfObjectionDate = null;
*/

    /**
     * @Form\AllowEmpty(true)
     * @Form\Required(true)
     * @Form\Attributes({"id":"opposerType","placeholder":"", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({
     *     "label": "Objector type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select an objector type",
     *     "category": "opposer_type",
     * })
     * @Form\Type("DynamicSelect")
     *
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "oppositionType",
     *          "context_values": {"otf_eob"},
     *          "validators": {
     *              {"name": "NotEmpty"}
     *          }
     *      }
     * })
     */
    public $opposerType = null;

    /**
     * @Form\Attributes({"id":"isValid","placeholder":""})
     * @Form\Options({
     *     "label": "Valid",
     *     "value_options": {"Y": "Yes", "N": "No", "U": "Not decided"},
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("Select")
     */
    public $isValid;

    /**
     * @Form\Attributes({"class":"extra-long","id":"validNotes"})
     * @Form\Options({"label":"Valid details"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":4000}})
     */
    public $validNotes;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Copied"})
     * @Form\Type("OlcsCheckbox")
     */
    public $isCopied;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Willing to attend PI"})
     * @Form\Type("OlcsCheckbox")
     */
    public $isWillingToAttendPi;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"In time"})
     * @Form\Type("OlcsCheckbox")
     */
    public $isInTime;

    /**
     * @Form\Attributes({"id":"status","placeholder":""})
     * @Form\Options({
     *     "label": "Status",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a status",
     *     "category": "opposition_status"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $status = null;

    /**
     * @Form\Attributes({"id":"affectedCentres","placeholder":"", "class":"chosen-select-medium",
     * "multiple":"multiple"})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Affected centre",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "context": "operatingCentre",
     *     "service_name": "Common/Service/Data/LicenceOperatingCentre",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $affectedCentres;

    /**
     * @Form\Required(false)
     * @Form\AllowEmpty(true)
     * @Form\Required(true)
     * @Form\Attributes({"id":"grounds","placeholder":"","class":"chosen-select-medium","required":false,
     *      "multiple":"multiple"})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({
     *     "label": "Grounds",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select grounds for opposition",
     *     "category": "obj_grounds"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $grounds = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"value":"ct_obj"})
     * @Form\Type("Hidden")
     */
    public $contactDetailsType = null;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Required(true)
     * @Form\Attributes({"id":"forename","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Contact first name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":35}})
     */
    public $forename = null;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Required(true)
     * @Form\Attributes({"id":"familyName","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Contact last name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":35}})
     */
    public $familyName = null;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Required(true)
     * @Form\Attributes({"id":"phone","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Phone"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":45}})
     */
    public $phone = null;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Required(true)
     * @Form\Attributes({"id":"email","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Email"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\EmailAddress"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":60}})
     */
    public $emailAddress = null;

    /**
     * @Form\Name("address")
     * @Form\Options({"label":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\RequestorsAddress")
     */
    public $address = null;

    /**
     * @Form\Attributes({"class":"extra-long","id":""})
     * @Form\Options({"label":"Notes"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":4000}})
     */
    public $notes = null;
}
