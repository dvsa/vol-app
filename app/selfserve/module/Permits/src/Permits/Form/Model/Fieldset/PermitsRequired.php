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
     * @Form\Type("Zend\Form\Element\Hidden")
     *  @Form\Options({
     *     "label": "permits.page.no-of-permits.for.year",
     * })
     */
    public $topLabel;

    /**
     * @Form\Name("requiredEuro5")
     * @Form\Filter({"name":"Common\Filter\NullToFloat"})
     * @Form\Attributes({
     *   "class" : "input--permits-required",
     *   "step" : "any"
     * })
     * @Form\Options({
     *     "hint": "permits.page.no-of-permits.for.euro5.vehicles",
     *     "short-label": "Euro 5",
     * })
     *
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "requiredEuro6",
     *          "context_values": {""},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name":"NotEmpty", "breakchainonfailure": true},
     *              {"name": "Zend\Validator\GreaterThan", "options": { "min":0, "message": { "notGreaterThan": "error.messages.permits.required.twoboxes"}, "breakchainonfailure": true}},
     *
     *          }
     *      }
     * })
     *
     * @Form\Validator(
     * {
     *      "name": "Digits",
     *      "options": {
     *          "break_chain_on_failure": true,
     *          "messages": {
     *              "digitsStringEmpty": "licence.surrender.current_discs.stolen.number.emptyMessage"
     *          }
     *      }
     * })
     *
     * @Form\Validator(
     *   {
     *     "name": "SumCompare",
     *     "options": {
     *          "sum_with":"requiredEuro6",
     *          "allow_empty": true,
     *          "compare_to":"numVehicles",
     *          "operator":"lte",
     *          "message":
     *              {"notNumberCompare": "permits.page.no-of-permits.error.max-exceeded"},
     *          "breakchainonfailure": true
     *      }
     *   }
     * )
     *
     * @Form\Type("Zend\Form\Element\Text")
     */
    public $requiredEuro5 = null;

    /**
     * @Form\Name("requiredEuro6")
     * @Form\Filter({"name":"Common\Filter\NullToFloat"})
     * @Form\Attributes({
     *   "class" : "input--permits-required",
     *   "step" : "any"
     * })
     * @Form\Options({
     *     "hint": "permits.page.no-of-permits.for.euro6.vehicles",
     *     "short-label": "Euro 6",
     * })
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "requiredEuro5",
     *          "context_values": {""},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name":"NotEmpty", "breakchainonfailure": true},
     *              {"name": "Zend\Validator\GreaterThan", "options": { "min":0, "message": { "notGreaterThan": "error.messages.permits.required.twoboxes"}, "breakchainonfailure": true}},
     *          }
     *      }
     * })
     *
     * @Form\Validator(
     * {
     *      "name": "Digits",
     *      "options": {
     *          "break_chain_on_failure": true,
     *          "messages": {
     *              "digitsStringEmpty": "licence.surrender.current_discs.stolen.number.emptyMessage"
     *          }
     *      }
     * })
     *
     * @Form\Validator(
     *   {
     *     "name": "SumCompare",
     *     "options": {
     *          "sum_with":"requiredEuro5",
     *          "allow_empty": true,
     *          "compare_to":"numVehicles",
     *          "operator":"lte",
     *          "message":
     *              {"notNumberCompare": "permits.page.no-of-permits.error.max-exceeded"},
     *          "breakchainonfailure": true
     *      }
     *   }
     * )
     *
     * @Form\Type("Zend\Form\Element\Text")
     */
    public $requiredEuro6 = null;

    /**
     * @Form\Type("Zend\Form\Element\Hidden")
     */
    public $numVehicles;
}
