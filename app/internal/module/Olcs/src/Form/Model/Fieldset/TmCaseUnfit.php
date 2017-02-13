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
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $decisionDate = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"required":false})
     * @Form\Options({
     *     "label": "Date of notification (to TM)",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\AllowEmpty(true)
     *
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "decisionDate",
     *          "context_values": {"--"},
     *          "context_truth": false,
     *          "allow_empty" : true,
     *          "validators": {
     *              {"name": "\Common\Validator\Date"},
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {
     *                  "name": "DateCompare",
     *                  "options": {
     *                      "compare_to":"decisionDate",
     *                      "compare_to_label":"Date of decision",
     *                      "operator": "gte",
     *                  }
     *              }
     *          }
     *      }
     * })
     *
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $notifiedDate = null;

    /**
     * @Form\Options({
     *     "label": "Unfitness start date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "max_year_delta": "+3",
     *     "min_year_delta": "-100",
     * })
     * @Form\Type("DateSelect")
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $unfitnessStartDate = null;

    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Unfitness end date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "max_year_delta": "+12",
     *     "min_year_delta": "-100",
     * })
     * @Form\Type("DateSelect")
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "unfitnessEndDate",
     *          "context_values": {"--"},
     *          "context_truth": false,
     *          "allow_empty" : true,
     *          "validators": {
     *              {
     *                  "name": "DateCompare",
     *                  "options": {
     *                      "compare_to":"unfitnessStartDate",
     *                      "operator":"gte",
     *                      "compare_to_label":"Unfitness start date"
     *                  }
     *              }
     *          }
     *      }
     * })
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $unfitnessEndDate = null;

    /**
     * @Form\Attributes({"id":"unfitnessReasons","class":"chosen-select-medium","multiple":"multiple"})
     * @Form\Options({
     *     "label": "Reason for unfitness",
     *     "disable_inarray_validator": false,
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
     *     "disable_inarray_validator": false,
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
