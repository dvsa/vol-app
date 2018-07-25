<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("PermitsRequired")
 */
class PermitsRequired
{
    /**
     * @Form\Name("PermitsRequired")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--permits-required",
     *   "id" : "PermitsRequired",
     *   "step" : "any"
     * })
     * @Form\Options({
     *     "label": "permits.form.permits.required.label",
     *     "hint": "",
     *     "short-label": "error.messages.permits.required",
     *      "allow_empty" : true,
     * })
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Validator({
     *     "name": "Permits\Form\Validator\CustomBetween",
     *     "options": {
     *          "min":1,
     *          "max":12,
     *          "too_small_message" : "error.messages.permits.required.too-small",
     *          "too_large_message" : "error.messages.permits.required.too-large",
     *     }})
     * @Form\Type("Zend\Form\Element\Number")
     */

    public $tripsAbroad = null;
}
