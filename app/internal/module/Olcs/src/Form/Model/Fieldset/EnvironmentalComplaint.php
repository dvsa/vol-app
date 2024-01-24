<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("environmental-complaint-fields")
 * @Form\Options({"label":"Environmental Complaint Details"})
 */
class EnvironmentalComplaint extends CaseBase
{
    /**
     * @Form\Attributes({"id":"complaintDate"})
     * @Form\Options({
     *     "label": "Complaint date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     * @Form\Type("DateSelect")
     */
    public $complaintDate = null;

    /**
     * @Form\Options({
     *     "label": "Description",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Type("TextArea")
     * @Form\Required(true)
     * @Form\Attributes({"id":"description","class":"extra-long","name":"description", "required":false})
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":5,"max":4000})
     */
    public $description = null;

    /**
     * @Form\Attributes({"id":"status","name":"status"})
     * @Form\Options({
     *     "label": "Complaint status",
     *     "disable_inarray_validator": false,
     *     "category": "env_complaint_status"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $status = null;

    /**
     * @Form\Attributes({"id":"operatingCentres","placeholder":"", "class":"chosen-select-medium",
     * "multiple":"multiple"})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Affected centre",
     *     "disable_inarray_validator": false,
     *     "context": "operatingCentre",
     *     "service_name": "Common\Service\Data\LicenceOperatingCentre",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Filter({"name":"Common\Filter\NullToArray"})
     */
    public $operatingCentres;

    /**
     * @Form\Attributes({"id":"complainantForename","class":"medium","name":"complainantForename"})
     * @Form\Options({"label":"Complainant first name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $complainantForename = null;

    /**
     * @Form\Attributes({"id":"complainantFamilyName","class":"medium","name":"complainantFamilyName"})
     * @Form\Options({"label":"Complainant family name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $complainantFamilyName = null;
}
