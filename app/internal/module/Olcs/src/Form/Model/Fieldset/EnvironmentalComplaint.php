<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

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
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     * @Form\Type("DateSelect")
     */
    public $complaintDate = null;

    /**
     * @Form\Attributes({"id":"complainantForename","class":"medium","name":"complainantForename"})
     * @Form\Options({"label":"Complainant first name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":35}})
     */
    public $complainantForename = null;

    /**
     * @Form\Attributes({"id":"complainantFamilyName","class":"medium","name":"complainantFamilyName"})
     * @Form\Options({"label":"Complainant family name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":35}})
     */
    public $complainantFamilyName = null;

    /**
     * @Form\Attributes({"id":"description","class":"extra-long","name":"description"})
     * @Form\Options({
     *     "label": "Description",
     *     "label_attributes": {
     *         "class": ""
     *     },
     *     "column-size": "",
     *     "help-block": "Complaint description"
     * })
     * @Form\Type("TextArea")
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":4000}})
     */
    public $description = null;

    /**
     * @Form\Attributes({"id":"status","name":"status"})
     * @Form\Options({
     *     "label": "Complaint status",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a status",
     *     "category": "complaint_status"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $status = null;
}
