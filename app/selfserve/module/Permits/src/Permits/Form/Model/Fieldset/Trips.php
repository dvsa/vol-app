<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Trips")
 */
class Trips
{
    /**
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--trips",
     *   "id" : "TripsAbroad",
     *   "step" : "any"
     * })
     * @Form\Options({
     *     "label": "permits.form.trips.label",
     *     "short-label": "",
     *     "hint" : "permits.form.trips.hint",
     * })
     * @Form\Validator({"name":"Zend\Validator\Digits"})
     * @Form\Validator({"name":"Zend\Validator\Between", "options": {"min": 0, "max": 999999}})
     * @Form\Type("Zend\Form\Element\Number")
     */
    public $tripsAbroad = null;
}
