<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("licence-decision-curtail")
 */
class LicenceStatusDecisionCurtail
{
    /**
     * @Form\Type("DateTimeSelect")
     * @Form\Filter({"name": "DateTimeSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({
     *     "name": "Date",
     *     "options": {
     *         "format": "Y-m-d H:i:s",
     *         "messages": {
     *             "dateInvalidDate": "datetime.compare.validation.message.invalid"
     *         }
     *     }
     * })
     * @Form\Required(true)
     * @Form\Options({
     *     "label": "licence-status.curtailment.from",
     *      "create_empty_option": true,
     *      "max_year_delta": "+10",
     *      "min_year_delta": "-5",
     * })
     * @Form\Attributes({"required":false})
     */
    public $curtailFrom = null;

    /**
     * @Form\Type("DateTimeSelect")
     * @Form\Filter({"name": "DateTimeSelectNullifier"})
     * @Form\Required(false)
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "curtailTo",
     *          "context_values": {"-- ::00"},
     *          "context_truth": false,
     *          "allow_empty" : true,
     *          "validators": {
     *              {"name": "\Common\Validator\Date"},
     *              {
     *                  "name": "Date",
     *                  "options": {
     *                      "format": "Y-m-d H:i:s",
     *                      "messages": {
     *                          "dateInvalidDate": "datetime.compare.validation.message.invalid"
     *                      }
     *                  },
     *                  "break_chain_on_failure": true,
     *              },
     *              {
     *                  "name": "DateCompare",
     *                  "options": {
     *                      "has_time": true,
     *                      "compare_to":"curtailFrom",
     *                      "operator":"gt",
     *                      "compare_to_label":"Curtail from"
     *                  }
     *              },
     *              {
     *                  "name": "DateInFuture",
     *              }
     *          }
     *      }
     * })
     * @Form\Options({
     *     "label": "licence-status.curtailment.to",
     *      "create_empty_option": true,
     *      "max_year_delta": "+10",
     *      "min_year_delta": "0",
     * })
     */
    public $curtailTo = null;
}
