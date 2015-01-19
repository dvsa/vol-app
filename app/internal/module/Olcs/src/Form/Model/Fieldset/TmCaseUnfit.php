<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class TmCaseUnfit extends CaseBase
{
    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "MSI",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     */
    public $isMsi;

    /**
     * @Form\Options({
     *     "label": "Date of decision",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Validator({"name":"Date", "options":{"format":"Y-m-d"}})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $decisionDate = null;

    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Date of notification (to TM)",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({
     *      "name": "DateCompare",
     *      "options": {"compare_to": "decisionDate", "operator":"gte", "compare_to_label": "Date of decision"}
     * })
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $notifiedDate = null;

    /**
     * @Form\Options({
     *     "label": "Unfitness start date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Validator({"name":"Date", "options":{"format":"Y-m-d"}})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $unfitnessStartDate = null;

    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Unfitness end date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Validator({"name":"Date", "options":{"format":"Y-m-d"}})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({
     *      "name": "DateCompare",
     *      "options": {
     *          "compare_to":"unfitnessStartDate",
     *          "operator":"gte",
     *          "compare_to_label":"Unfitness start date"}
     * })
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $unfitnessEndDate = null;

    /**
     * @Form\Attributes({"id":"unfitnessReasons","class":"chosen-select-medium","multiple":"multiple"})
     * @Form\Options({
     *     "label": "Reason for unfitness",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a reason for unfitness",
     *     "category": "tm_unfit_reason"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $unfitnessReasons = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"rehabMeasures","class":"chosen-select-medium","multiple":"multiple"})
     * @Form\Options({
     *     "label": "Rehabilitation measure",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a rehabilitation measure",
     *     "category": "tm_case_rehab"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $rehabMeasures = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $decision = null;
}
