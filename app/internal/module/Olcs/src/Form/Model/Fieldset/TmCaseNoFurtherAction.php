<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

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
     * @Form\Required(false)
     * @Form\Attributes({"class":"extra-long"})
     * @Form\Options({"label":"Reason why no further action"})
     * @Form\Type("Textarea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":500}})
     */
    public $noFurtherActionReason = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $decision = null;
}
