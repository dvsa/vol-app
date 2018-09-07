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
     * @Form\Name("permitsRequired")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--permits-required",
     *   "id" : "PermitsRequired",
     *   "step" : "any"
     * })
     * @Form\Options({
     *     "label": "permits.form.permits.required.label",
     *     "hint": "permits.form.permits-required.hint",
     *     "short-label": "",
     * })
     * @Form\Validator({"name":"Zend\Validator\Digits"})
     * @Form\Validator({"name":"Zend\Validator\GreaterThan", "options": {"min": 0}})
     * @Form\Validator({
     *     "name": "NumberCompare",
     *     "options": {
     *          "compare_to":"numVehicles",
     *          "operator":"lte",
     *          "compare_to_label":"Your number of authorised vehicles",
     *     }
     * })
     * @Form\Type("Zend\Form\Element\Number")
     */
    public $permitsRequired = null;

    /**
     * @Form\Type("Zend\Form\Element\Hidden")
     */
    public $numVehicles;
}
