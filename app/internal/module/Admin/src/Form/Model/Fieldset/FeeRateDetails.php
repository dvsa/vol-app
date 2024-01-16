<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;
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
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("Date", options={"format": "Y-m-d"})
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
     * @Form\Type("Laminas\Form\Element\Number")
     * @Form\Validator({
     *      "name": "Laminas\Validator\Between",
     *      "options": {
     *          "min": "0",
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
     * @Form\Validator("Laminas\Validator\Digits")
     * @Form\Type("Laminas\Form\Element\Number")
     * @Form\Validator({
     *      "name": "Laminas\Validator\Between",
     *      "options": {
     *          "min": "0",
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
     * @Form\Validator("Laminas\Validator\Digits")
     * @Form\Type("Laminas\Form\Element\Number")
     * @Form\Validator({
     *      "name": "Laminas\Validator\Between",
     *      "options": {
     *          "min": "0",
     *          "max": "9999"
     *      }
     * })
     */
    public $fiveYearValue = null;
}
