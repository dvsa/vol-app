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
     * @Form\Validator({
     *     "name": "Zend\Validator\NotEmpty",
     *     "options": {
     *         "message": {
     *             "isEmpty": "error.messages.permits.required"
     *         },
     *         "breakchainonfailure": true
     *     },
     * })
     * @Form\Validator({
     *     "name": "Zend\Validator\Digits",
     *     "options": {
     *         "message": {
     *             "notDigits": "error.messages.permits.not.digits"
     *         },
     *         "breakchainonfailure": true
     *     }
     * })
     * @Form\Validator({
     *     "name": "Zend\Validator\GreaterThan",
     *     "options": {
     *         "min":0,
     *         "message": {
     *             "notGreaterThan": "error.messages.permits.greater"
     *         },
     *         "breakchainonfailure": true
     *     },
     * })
     * @Form\Validator({
     *     "name": "NumberCompare",
     *     "options": {
     *         "compare_to":"numVehicles",
     *         "operator":"lte",
     *         "message": {
     *             "notNumberCompare": "error.messages.permits.less"
     *         }
     *     }
     * })
     *
     * @Form\Type("Zend\Form\Element\Number")
     */
    public $permitsRequired = null;

    /**
     * @Form\Type("Zend\Form\Element\Hidden")
     */
    public $numVehicles;
}
