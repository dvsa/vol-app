<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("\Common\Form\Elements\Types\RadioHorizontal")
 * @Form\Options({"label" : "continuations.finances.overdraftFacility.label"})
 */
class OverdraftFacility
{
    /**
     * @Form\Type("Common\Form\Elements\Types\RadioYesNo")
     * @Form\Options({
     *     "label": "continuations.finances.overdraftFacility.label",
     * })
     * @Form\ErrorMessage("continuations.finances.overdraftFacility.error")
     */
    public $yesNo;

    /**
     * @Form\Type("Text")
     * @Form\Attributes({"id":"overDraftLimit"})
     * @Form\Required(true)
     * @Form\Options({
     *     "label": "continuations.finances.overdraftFacility.amount.label",
     * })
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "yesNo",
     *          "context_values": {"Y"},
     *          "validators": {
     *              {
     *                  "name": "NotEmpty",
     *                  "options": {
     *                      "messages": {"isEmpty" : "continuations.finances.overdraftFacility.amount.empty"},
     *                      "break_chain_on_failure": true,
     *                  }
     *              },
     *              {
     *                  "name": "Dvsa\Olcs\Transfer\Validators\Money",
     *                  "options": {
     *                      "messages": {"invalid": "continuations.finances.overdraftFacility.amount.notNumber"},
     *                      "allow_negative" : true,
     *                      "break_chain_on_failure": true,
     *                  }
     *              },
     *              {
     *                  "name": "GreaterThan",
     *                  "options": {
     *                      "min" : 0,
     *                      "messages": {
     *                          "notGreaterThan": "continuations.finances.overdraftFacility.amount.notGreaterThan"
     *                      }
     *                  }
     *              },
     *              {
     *                  "name": "LessThan",
     *                  "options": {
     *                      "max": 10000000000,
     *                      "messages": {"notLessThan": "continuations.finances.overdraftFacility.amount.notLessThan"}
     *                  }
     *              },
     *          }
     *      }
     * )
     */
    public $yesContent;
}
