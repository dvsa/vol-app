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
     *              {"name": "Digits"}
     *          }
     *      }
     * })
     */
    public $number = null;
}
