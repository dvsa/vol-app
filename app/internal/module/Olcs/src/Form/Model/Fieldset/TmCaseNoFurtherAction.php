<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class TmCaseNoFurtherAction extends CaseBase
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
     * @Form\Required(true)
     * @Form\Type("DateSelect")
     * @Form\Options({
     *     "label": "Date of decision",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Filter("DateSelect", options={"null_on_empty": true})
     * @Form\Validator({"name": "\Common\Validator\Date"})
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
     */
    public $notifiedDate = null;

    /**
     * @Form\Required(false)
     * @Form\Type("Textarea")
     * @Form\Attributes({"class":"extra-long"})
     * @Form\Options({"label":"Reason why no further action"})
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":500})
     */
    public $noFurtherActionReason = null;

    /**
     * @Form\Type("Hidden")
     * @Form\Attributes({"value":""})
     */
    public $decision = null;
}
