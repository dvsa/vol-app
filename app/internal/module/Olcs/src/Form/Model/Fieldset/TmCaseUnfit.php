<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

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
     * @Form\Type("DateSelect")
     * @Form\Options({
     *     "label": "Date of decision",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $decisionDate = null;

    /**
     * @Form\Required(true)
     * @Form\Type("DateSelect")
     * @Form\Attributes({"required":false})
     * @Form\Options({
     *     "label": "Date of notification (to TM)",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Filter("DateSelect", options={"null_on_empty": true})
     * @Form\Validator("NotEmpty", options={"array"})
     * @Form\Validator("ValidateIf",
     *      options={
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
     * )
     */
    public $notifiedDate = null;

    /**
     * @Form\Type("DateSelect")
     * @Form\Options({
     *     "label": "Unfitness start date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "max_year_delta": "+3",
     *     "min_year_delta": "-100",
     * })
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $unfitnessStartDate = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Options({
     *     "label": "Unfitness end date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "max_year_delta": "+12",
     *     "min_year_delta": "-100",
     * })
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("ValidateIf",
     *      options={
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
     * )
     */
    public $unfitnessEndDate = null;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"unfitnessReasons","class":"chosen-select-medium","multiple":"multiple"})
     * @Form\Options({
     *     "label": "Reason for unfitness",
     *     "disable_inarray_validator": false,
     *     "category": "tm_unfit_reason"
     * })
     */
    public $unfitnessReasons = [];

    /**
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"rehabMeasures","class":"chosen-select-medium","multiple":"multiple"})
     * @Form\Options({
     *     "label": "Rehabilitation measure",
     *     "disable_inarray_validator": false,
     *     "category": "tm_case_rehab"
     * })
     * @Form\Filter("Common\Filter\NullToArray")
     */
    public $rehabMeasures = [];

    /**
     * @Form\Type("Hidden")
     * @Form\Attributes({"value":""})
     */
    public $decision = null;
}
