<?php

namespace Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset;

use Laminas\Form\Annotation as Form;

class StolenInfo
{
    /**
     * @Form\Name("number")
     * @Form\Required(true)
     * @Form\ContinueIfEmpty(true)
     * @Form\Options({
     *     "label":"licence.surrender.current_discs.stolen.number.label",
     * })
     * @Form\Attributes({
     *      "class":"govuk-input govuk-!-width-one-third",
     * })
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "stolen",
     *          "context_values": {"Y"},
     *          "inject_post_data": "stolenSection->stolen",
     *          "validators": {
     *              {
     *                  "name": "NotEmpty",
     *                  "options": {
     *                      "messages": {"isEmpty" : "Value is required and can't be empty"},
     *                  }
     *              },
     *              {
     *                  "name": "Digits",
     *                  "options": {
     *                      "break_chain_on_failure": true,
     *                      "messages": {
     *                          "digitsStringEmpty": "licence.surrender.current_discs.stolen.number.emptyMessage"
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
     * )
     */
    public $number = null;

    /**
     * @Form\Name("details")
     * @Form\Required(true)
     * @Form\AllowEmpty(true)
     * @Form\ContinueIfEmpty(true)
     * @Form\Type("textarea")
     * @Form\Options({
     *     "label":"licence.surrender.current_discs.stolen.details.label",
     *     "hint":"licence.surrender.current_discs.stolen.details.hint"
     * })
     * @Form\Attributes({
     *     "class":"govuk-textarea",
     *     "rows":"5"
     * })
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "stolen",
     *          "context_values": {"Y"},
     *          "inject_post_data": "stolenSection->stolen",
     *          "validators": {
     *              {
     *                  "name": "Laminas\Validator\StringLength",
     *                  "options": {
     *                      "min": 1,
     *                      "max": 500,
     *                      "messages" : {
     *                          "stringLengthTooShort": "licence.surrender.current_discs.stolen.details.stringLengthTooShort",
     *                          "stringLengthTooLong": "licence.surrender.current_discs.details.stringLengthTooLong",
     *                      }
     *                  }
     *              }
     *          }
     *      }
     * )
     */
    public $details = null;
}
