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
     * @Form\Validator({
     *      "name": "Zend\Validator\NotEmpty",
     *      "options": {
     *          "message": {
     *              "isEmpty": "error.messages.trips.empty"
     *          }
     *     }
     * })
     * @Form\Validator({
     *      "name": "Zend\Validator\Digits",
     *      "options": {
     *          "message": {
     *              "notDigits": "error.messages.trips.not.digits"
     *          }
     *     }
     * })
     * @Form\Validator({
     *      "name": "Zend\Validator\Between",
     *      "options": {
     *          "min": 0,
     *          "max": 999999,
     *          "message": {
     *              "notBetween": "error.messages.trips.not.between"
     *          }
     *     }
     * })


     * @Form\Type("Zend\Form\Element\Number")
     */
    public $tripsAbroad = null;
}
