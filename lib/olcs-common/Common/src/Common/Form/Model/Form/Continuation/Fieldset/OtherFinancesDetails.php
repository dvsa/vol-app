<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Class OtherAvailableBalanceDetails
 */
class OtherFinancesDetails
{
    /**
     * @Form\Type("Text")
     * @Form\Attributes({"id":"otherFinances_amount"})
     * @Form\Required(true)
     * @Form\Options({
     *     "label": "continuations.finances.otherFinances.amount.label",
     *     "hint": "continuations.finances.otherFinances.amount.hint",
     * })
     * @Form\Validator("NotEmpty", options={"null"})
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "yesNo",
     *          "context_values": {"Y"},
     *          "inject_post_data": "finances->otherFinances->yesNo",
     *          "validators": {
     *              {
     *                  "name": "NotEmpty",
     *                  "options": {
     *                      "messages": {"isEmpty" : "continuations.finances.otherFinances.amount.empty"},
     *                      "break_chain_on_failure": true,
     *                  }
     *              },
     *              {
     *                  "name": "Dvsa\Olcs\Transfer\Validators\Money",
     *                  "options": {
     *                      "messages": {"invalid": "continuations.finances.otherFinances.amount.notNumber"},
     *                      "allow_negative" : true,
     *                      "break_chain_on_failure": true,
     *                  }
     *              },
     *              {
     *                  "name": "GreaterThan",
     *                  "options": {
     *                      "min" : 0,
     *                      "messages": {"notGreaterThan": "continuations.finances.otherFinances.amount.notGreaterThan"}
     *                  }
     *              },
     *              {
     *                  "name": "LessThan",
     *                  "options": {
     *                      "max": 10000000000,
     *                      "messages": {"notLessThan": "continuations.finances.otherFinances.amount.notLessThan"}
     *                  }
     *              },
     *          }
     *      }
     * )
     */
    public $amount;

    /**
     * @Form\Type("Textarea")
     * @Form\Attributes({"id":"otherFinances_detail"})
     * @Form\Required(true)
     * @Form\Options({
     *     "label": "continuations.finances.otherFinances.detail.label",
     * })
     * @Form\Validator("NotEmpty", options={"null"})
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "yesNo",
     *          "context_values": {"Y"},
     *          "inject_post_data": "finances->otherFinances->yesNo",
     *          "validators": {
     *              {
     *                  "name": "NotEmpty",
     *                  "options": {
     *                      "messages": {"isEmpty" : "continuations.finances.otherFinances.detail.empty"},
     *                      "break_chain_on_failure": true,
     *                  }
     *              },
     *              {
     *                  "name": "StringLength",
     *                  "options": {
     *                      "min" : 1,
     *                      "max" : 200,
     *                  }
     *              },
     *          }
     *      }
     * )
     */
    public $detail;
}
