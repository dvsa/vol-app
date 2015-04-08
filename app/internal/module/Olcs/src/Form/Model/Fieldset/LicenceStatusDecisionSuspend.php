<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-decision-suspend")
 */
class LicenceStatusDecisionSuspend
{
    /**
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name":"Date", "options":{"format":"Y-m-d"}})
     * @Form\Required(true)
     * @Form\Options({
     *     "label": "licence-status.suspension.from",
     *      "create_empty_option": true,
     *      "max_year_delta": "+10",
     *      "min_year_delta": "-5",
     * })
     * @Form\Attributes({"required":false})
     */
    public $suspendFrom = null;

    /**
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Required(false)
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "suspendTo",
     *          "context_values": {"--"},
     *          "context_truth": false,
     *          "allow_empty" : true,
     *          "validators": {
     *              {"name":"Date", "options":{"format":"Y-m-d"}},
     *              {
     *                  "name": "DateCompare",
     *                  "options": {
     *                      "compare_to":"suspendFrom",
     *                      "operator":"gt",
     *                      "compare_to_label":"Suspend from"
     *                  }
     *              }
     *          }
     *      }
     * })
     * @Form\Options({
     *     "label": "licence-status.suspension.to",
     *      "create_empty_option": true,
     *      "max_year_delta": "+10",
     *      "min_year_delta": "-5"
     * })
     */
    public $suspendTo = null;
}
