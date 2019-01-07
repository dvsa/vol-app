<?php

namespace Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset;

use Zend\Form\Annotation as Form;

class LostInfo
{
    /**
     * @Form\Name("number")
     * @Form\Required(true)
     * @Form\AllowEmpty(true)
     * @Form\ContinueIfEmpty(true)
     * @Form\Type("Number")
     * @Form\Options({
     *     "label": "Number of discs lost",
     * })
     * @Form\Attributes({
     *      "class": "govuk-input govuk-!-width-one-third",
     *      "step": "any",
     *      "min": 0
     * })
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "lost",
     *          "context_values": {"Y"},
     *          "inject_post_data": "lostSection->lost",
     *          "validators": {
     *              {
     *                  "name": "Digits",
     *                  "options": {
     *                      "break_chain_on_failure": true,
     *                      "messages": {
     *                          "digitsStringEmpty": "licence.surrender.current_discs.lost.number.emptyMessage"
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

    /**
     * @Form\Name("details")
     * @Form\Required(true)
     * @Form\AllowEmpty(true)
     * @Form\ContinueIfEmpty(true)
     * @Form\Type("textarea")
     * @Form\Options({
     *     "label": "Please provide details of lost documents",
     *     "hint": "licence.surrender.current_discs.lost.details.hint"
     * })
     * @Form\Attributes({
     *     "class": "govuk-textarea",
     *     "rows": "5"
     * })
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "lost",
     *          "context_values": {"Y"},
     *          "inject_post_data": "lostSection->lost",
     *          "validators": {
     *              {
     *                  "name": "Zend\Validator\StringLength",
     *                  "options": {
     *                      "min": 1,
     *                      "max": 500,
     *                      "messages" : {
     *                          "stringLengthTooShort": "licence.surrender.current_discs.lost.details.stringLengthTooShort",
     *                          "stringLengthTooLong": "licence.surrender.current_discs.details.stringLengthTooLong",
     *                      }
     *                  }
     *              }
     *          }
     *      }
     * })
     */
    public $details = null;
}
