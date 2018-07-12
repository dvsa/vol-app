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
     * })
     * @Form\Options({
     *     "label": "permits.form.permits.required.label",
     *     "hint": "",
     *     "short-label": "error.messages.permits.required",
     * })
     * @Form\Type("Zend\Form\Element\Number")
     */

    public $tripsAbroad = null;
}
