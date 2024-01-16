<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("complaint-fields")
 * @Form\Options({"label":"Complaint Details"})
 */
class Complaint extends CaseBase
{
    /**
     * @Form\Attributes({"id":"complainantForename","class":"medium","name":"complainantForename"})
     * @Form\Options({"label":"Complainant first name"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $complainantForename = null;

    /**
     * @Form\Attributes({"id":"complainantFamilyName","class":"medium","name":"complainantFamilyName"})
     * @Form\Options({"label":"Complainant family name"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $complainantFamilyName = null;

    /**
     * @Form\Attributes({"id":"complaintDate"})
     * @Form\Options({
     *     "label": "Complaint date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator("\Common\Form\Elements\Validators\DateNotInFuture")
     * @Form\Type("DateSelect")
     */
    public $complaintDate = null;

    /**
     * @Form\Attributes({"id":"complaintType"})
     * @Form\Options({
     *     "label": "Complaint type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "complaint_type"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $complaintType = null;

    /**
     * @Form\Attributes({"id":"status","name":"status"})
     * @Form\Options({
     *     "label": "Complaint status",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "complaint_status"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $status = null;

    /**
     * @Form\Attributes({"id":"description","class":"extra-long","name":"description"})
     * @Form\Options({
     *     "label": "Description",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Type("TextArea")
     * @Form\Required(false)
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":5,"max":4000})
     */
    public $description = null;

    /**
     * @Form\Attributes({"id":"vrm","class":"medium","name":"vrm"})
     * @Form\Options({
     *     "label": "Vehicle registration mark",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Type("Text")
     * @Form\Required(false)
     */
    public $vrm = null;

    /**
     * @Form\Attributes({"placeholder":"","class":"medium","name":"driverForename"})
     * @Form\Options({"label":"Driver first name"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $driverForename = null;

    /**
     * @Form\Attributes({"placeholder":"","class":"medium","name":"driverFamilyName"})
     * @Form\Options({"label":"Driver family name"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $driverFamilyName = null;

    /**
     * @Form\Attributes({"id":"closedDate"})
     * @Form\Options({
     *     "label": "Close date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator("\Common\Form\Elements\Validators\DateNotInFuture")
     */
    public $closedDate = null;
}
