<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;
use Common\Form\Model\Form\Traits\IdTrait;

/**
 * @codeCoverageIgnore No methods
 */
class FeeRateDetails
{
    use IdTrait;

    /**
     * @Form\Type("\Common\Form\Elements\Types\Readonly")
     * @Form\Options({
     *     "label": "ID:"
     * })
     *
     */
    public $idReadOnly;

    /**
     * @Form\Type("\Common\Form\Elements\Types\Readonly")
     * @Form\Options({
     *     "label": "Fee Type:"
     * })
     *
     */
    public $feeType;

    /**
     * @Form\Type("\Common\Form\Elements\Types\Readonly")
     * @Form\Options({
     *     "label": "Description:"
     * })
     *
     */
    public $description;

    /**
     * @Form\Options({
     *     "label": "Effective From:",
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required":true})
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({
     *      "name": "Dvsa\Olcs\Transfer\Validators\DateInFuture",
     *      "options": {
     *          "include_today": true,
     *          "use_time": false
     *      }
     * })
     */
    public $effectiveFrom = null;

    /**
     * @Form\Name("fixedValue")
     * @Form\Attributes({"required": true, "min": 0, "step": 1})
     * @Form\Options({
     *      "label":"Fixed Value",
     * })
     * @Form\Validator({"name":"Zend\Validator\Digits"})
     * @Form\Type("Zend\Form\Element\Number")
     * @Transfer\Validator({
     *      "name": "Zend\Validator\GreaterThan",
     *      "options": {
     *          "min": "-1",
     *          "max": "9999"
     *      }
     * })
     */
    public $fixedValue = null;

    /**
     * @Form\Name("annualValue")
     * @Form\Attributes({"required": true, "min": 0, "step": 1})
     * @Form\Options({
     *      "label":"Annual Value",
     * })
     * @Form\Validator({"name":"Zend\Validator\Digits"})
     * @Form\Type("Zend\Form\Element\Number")
     * @Transfer\Validator({
     *      "name": "Zend\Validator\GreaterThan",
     *      "options": {
     *          "min": "-1",
     *          "max": "9999"
     *      }
     * })
     */
    public $annualValue = null;

    /**
     * @Form\Name("fiveYearValue")
     * @Form\Attributes({"required": true, "min": 0, "step": 1})
     * @Form\Options({
     *      "label":"Five Year Value",
     * })
     * @Form\Validator({"name":"Zend\Validator\Digits"})
     * @Form\Type("Zend\Form\Element\Number")
     * @Transfer\Validator({
     *      "name": "Zend\Validator\GreaterThan",
     *      "options": {
     *          "min": "-1",
     *          "max": "9999"
     *      }
     * })
     */
    public $fiveYearValue = null;
}
