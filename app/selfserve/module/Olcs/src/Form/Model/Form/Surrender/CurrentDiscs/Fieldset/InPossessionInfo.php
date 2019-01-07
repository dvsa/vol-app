<?php

namespace Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset;

use Zend\Form\Annotation as Form;

class InPossessionInfo
{
    /**
     * @Form\Name("number")
     * @Form\Required(true)
     * @Form\AllowEmpty(true)
     * @Form\ContinueIfEmpty(true)
     * @Form\Type("Number")
     * @Form\Options({
     *     "label":"Number of discs you will destroy",
     * })
     * @Form\Attributes({
     *      "class":"govuk-input govuk-!-width-one-third",
     *      "step": "any",
     *      "min": 0
     * })
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "inPossession",
     *          "context_values": {"Y"},
     *          "inject_post_data": "possessionSection->inPossession",
     *          "validators": {
     *              {
     *                  "name": "Digits",
     *                  "options": {
     *                      "break_chain_on_failure": true,
     *                      "messages": {
     *                          "digitsStringEmpty": "licence.surrender.current_discs.destroy.number.emptyMessage"
     *                      }
     *                  }
     *              },
     *              {
     *                  "name": "GreaterThan",
     *                  "options": {
     *                      "min": 0,
     *                      "messages": {
     *                          "notGreaterThan": "licence.surrender.current_discs.number.greaterThanMessage"
     *                      }
     *                  }
     *              }
     *          }
     *      }
     * })
     */
    public $number = null;
}
