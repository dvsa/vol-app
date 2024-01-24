<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class OppositionFields extends CaseBase
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Type("Hidden")
     * @Form\Attributes({"value":""})
     */
    public $version = null;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"oppositionType","placeholder":""})
     * @Form\Options({
     *     "label": "Opposition type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "obj_type"
     * })
     * @Form\Filter({"name":"Common\Filter\NullToArray"})
     */
    public $oppositionType;

    /**
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Attributes({"id":"contactDetailsDescription","placeholder":"","class":"extra-long"})
     * @Form\Options({"label":"Objector body"})
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min": 5, "max":255})
     */
    public $contactDetailsDescription = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Attributes({"id":"raisedDate"})
     * @Form\Options({
     *     "label": "Date received",
     *     "create_empty_option": true,
     *     "render_delimiters": true
     * })
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $raisedDate = null;

    /**
     * @Form\Required(false)
     * @Form\Type("Common\Form\Elements\Types\Html")
     * @Form\Attributes({"id":"outOfRepresentationDate", "required": false})
     */
    public $outOfRepresentationDate = null;

    /**
     * @Form\Required(false)
     * @Form\Type("Common\Form\Elements\Types\Html")
     * @Form\Attributes({"id":"outOfObjectionDate", "required": false})
     */
    public $outOfObjectionDate = null;

    /**
     * @Form\Required(true)
     * @Form\Type("DynamicSelect")
     * @Form\Options({
     *     "label": "Objector type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "opposer_type",
     * })
     * @Form\Attributes({"id":"opposerType","placeholder":"", "required":false})
     * @Form\Filter({"name":"Common\Filter\NullToArray"})
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"array"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "oppositionType",
     *          "context_values": {"otf_eob", "otf_obj"},
     *          "validators": {
     *              {"name": "NotEmpty"}
     *          }
     *      }
     * })
     */
    public $opposerType = null;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"isValid","placeholder":""})
     * @Form\Options({
     *     "label": "Valid",
     *     "disable_inarray_validator": false,
     *     "category": "opposition_valid",
     * })
     * @Form\Filter({"name":"Common\Filter\NullToArray"})
     */
    public $isValid;

    /**
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Attributes({"class":"extra-long","id":"validNotes"})
     * @Form\Options({"label":"Valid details"})
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":4000})
     */
    public $validNotes;

    /**
     * @Form\Type("OlcsCheckbox")
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Copied"})
     */
    public $isCopied;

    /**
     * @Form\Type("OlcsCheckbox")
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Willing to attend PI"})
     */
    public $isWillingToAttendPi;

    /**
     * @Form\Type("OlcsCheckbox")
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"In time"})
     */
    public $isInTime;

    /**
     * @Form\Type("OlcsCheckbox")
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Withdrawn"})
     */
    public $isWithdrawn;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"status","placeholder":""})
     * @Form\Options({
     *     "label": "Status",
     *     "disable_inarray_validator": false,
     *     "category": "opposition_status"
     * })
     * @Form\Filter({"name":"Common\Filter\NullToArray"})
     */
    public $status = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"licenceOperatingCentres","placeholder":"", "class":"chosen-select-medium",
     * "multiple":"multiple"})
     * @Form\Options({
     *     "label": "Affected centre",
     *     "context":"licence",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\OcContextListDataService",
     *     "use_groups": "false"
     * })
     * @Form\Filter({"name":"Common\Filter\NullToArray"})
     */
    public $licenceOperatingCentres;

    /**
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"applicationOperatingCentres","placeholder":"", "class":"chosen-select-medium",
     * "multiple":"multiple"})
     * @Form\Options({
     *     "label": "Affected centre",
     *     "context":"application",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\OcContextListDataService",
     *     "use_groups": "false"
     * })
     * @Form\Filter({"name":"Common\Filter\NullToArray"})
     */
    public $applicationOperatingCentres;

    /**
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     * @Form\Options({
     *     "label": "Grounds",
     *     "disable_inarray_validator": false,
     *     "category": "obj_grounds"
     * })
     * @Form\Attributes({"id":"grounds","placeholder":"","class":"chosen-select-medium","required":false,
     *      "multiple":"multiple"})
     * @Form\Filter({"name":"Common\Filter\NullToArray"})
     */
    public $grounds = null;

    /**
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Attributes({"class":"extra-long","id":""})
     * @Form\Options({"label":"Notes"})
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":4000})
     */
    public $notes = null;
}
