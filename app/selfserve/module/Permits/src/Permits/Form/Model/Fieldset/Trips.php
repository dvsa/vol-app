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
     * @Form\Name("TripsAbroad")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--trips",
     *   "id" : "TripsAbroad",
     * })
     * @Form\Options({
     *     "label": "permits.form.trips.label",
     *     "hint": "For licence OB2013691 (North East of England)",
     *     "short-label": "error.messages.trips",
     * })
     * @Form\Validator({"name": "Permits\Form\Validator\CustomBetween", "options": {"min":1, "max":999999}})
     * @Form\Type("Zend\Form\Element\Number")
     */

    public $tripsAbroad = null;
}
