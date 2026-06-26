<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Class FactoringDetails
 */
class FactoringDetails
{
    /**
     * @Form\Type("Text")
     * @Form\Attributes({"id":"factoring_amount"})
     * @Form\Required(true)
     * @Form\Options({
     *     "label": "continuations.finances.factoring.amount.label",
     *     "hint": "continuations.finances.factoring.amount.hint",
     * })
     * @Form\Validator("NotEmpty", options={"null"})
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "yesNo",
     *          "context_values": {"Y"},
     *          "inject_post_data": "finances->factoring->yesNo",
     *          "validators": {
     *              {
     *                  "name": "NotEmpty",
     *                  "options": {
     *                      "messages": {"isEmpty" : "continuations.finances.factoring.amount.empty"},
     *                      "break_chain_on_failure": true,
     *                  }
     *              },
     *              {
     *                  "name": "Dvsa\Olcs\Transfer\Validators\Money",
     *                  "options": {
     *                      "messages": {"invalid": "continuations.finances.factoring.amount.notNumber"},
     *                      "allow_negative" : true,
     *                      "break_chain_on_failure": true,
     *                  }
     *              },
     *              {
     *                  "name": "GreaterThan",
     *                  "options": {
     *                      "min" : 0,
     *                      "messages": {"notGreaterThan": "continuations.finances.factoring.amount.notGreaterThan"}
     *                  }
     *              },
     *              {
     *                  "name": "LessThan",
     *                  "options": {
     *                      "max": 10000000000,
     *                      "messages": {"notLessThan": "continuations.finances.factoring.amount.notLessThan"}
     *                  }
     *              },
     *          }
     *      }
     * )
     */
    public $amount;
}
