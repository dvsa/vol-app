<?php

namespace Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset;

use Zend\Form\Annotation as Form;

class StolenInfo
{
    /**
     * @Form\Name("number")
     * @Form\Required(true)
     * @Form\AllowEmpty(true)
     * @Form\ContinueIfEmpty(true)
     * @Form\Type("Number")
     * @Form\Options({
     *     "label":"Number of discs stolen",
     * })
     * @Form\Attributes({
     *      "class":"govuk-input govuk-!-width-one-third",
     *      "step": "any",
     *      "min": 0
     * })
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "stolen",
     *          "context_values": {"Y"},
     *          "inject_post_data": "stolenSection->stolen",
     *          "validators": {
     *              {"name": "Digits"}
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
     *     "label":"Please provide details of stolen documents",
     *     "hint":"Don’t include personal or financial information, eg your National Insurance number or credit card details."
     * })
     * @Form\Attributes({
     *     "class":"govuk-textarea",
     *     "rows":"5"
     * })
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "stolen",
     *          "context_values": {"Y"},
     *          "inject_post_data": "stolenSection->stolen",
     *          "validators": {
     *              {
     *                  "name": "Zend\Validator\StringLength",
     *                  "options": {
     *                      "min": 1,
     *                      "max": 500
     *                  }
     *              }
     *          }
     *      }
     * })
     */
    public $details = null;
}
