<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class Stay extends CaseBase
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $stayType = null;

    /**
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Date of request",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $requestDate = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Attributes({"id":"decisionDate","class":"extra-long"})
     * @Form\Options({
     *     "label": "Date of decision",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "requestDate",
     *          "context_values": {"--"},
     *          "context_truth": false,
     *          "allow_empty" : true,
     *          "validators": {
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {
     *                  "name": "DateCompare",
     *                  "options": {
     *                      "compare_to":"requestDate",
     *                      "compare_to_label":"Date of request",
     *                      "operator": "gte",
     *                  }
     *              }
     *          }
     *      }
     * )
     */
    public $decisionDate = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"DVSA/DVA notified?"})
     * @Form\Type("OlcsCheckbox")
     */
    public $dvsaNotified = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Outcome",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "stay_status"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $outcome = null;

    /**
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Notes"
     * })
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":5,"max":4000})
     */
    public $notes = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Cancelled / Withdrawn?"})
     * @Form\Type("OlcsCheckbox")
     */
    public $isWithdrawn = null;

    /**
     * @Form\Required(true)
     * @Form\Type("DateSelect")
     * @Form\Attributes({"id":"withdrawnDate", "required":false})
     * @Form\Options({
     *     "label": "Withdrawn date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Filter("DateSelect", options={"null_on_empty":true})
     * @Form\Validator("NotEmpty", options={"array"})
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "isWithdrawn",
     *          "context_values": {"Y"},
     *          "validators": {
     *              {"name": "NotEmpty"},
     *              {"name": "\Common\Validator\Date"},
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {"name": "\Common\Form\Elements\Validators\DateNotInFuture"}
     *          }
     *      }
     * )
     */
    public $withdrawnDate = null;
}
