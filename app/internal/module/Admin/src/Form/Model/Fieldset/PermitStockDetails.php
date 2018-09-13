<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;
use Common\Form\Model\Form\Traits\IdTrait;

/**
 * @codeCoverageIgnore No methods
 */
class PermitStockDetails
{
    use IdTrait;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Name("permitType")
     * @Form\Attributes({"id":"permitType","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Permit Type",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\IrhpPermitType",
     *     "required": true
     * })
     */
    public $permitType = null;

    /**
     * @Form\Type("DateSelect")
     * @Form\Name("validFrom")
     * @Form\Options({
     *      "label": "Validity period start",
     *      "create_empty_option": true,
     *      "max_year_delta": "+5",
     *      "required": true
     * })
     * @Form\Attributes({"id": "validFrom"})
     * @Form\Validator({"name": "Date", "options": {"format": "d-m-Y"}})
     */
    public $validFrom = null;

    /**
     * @Form\Type("DateSelect")
     * @Form\Name("validTo")
     * @Form\Options({
     *      "label": "Validity period end",
     *      "create_empty_option": true,
     *      "max_year_delta": "+5",
     *      "required": true
     * })
     * @Form\Attributes({"id": "validTo"})
     * @Form\Validator({"name": "Date", "options": {"format": "d-m-Y"}})
     */
    public $validTo = null;

    /**
     * @Form\Name("initialStock")
     * @Form\Attributes({"id": "initialStock"})
     * @Form\Options({
     *      "label": "Quota"
     * })
     * @Form\Type("Text")
     * @Transfer\Filter({"name":"Zend\Filter\Digits"})
     * @Transfer\Validator({"name":"Zend\Validator\Digits"})
     * @Transfer\Validator({
     *      "name":"Zend\Validator\GreaterThan",
     *      "options": {
     *          "min": 0,
     *          "max": 9999999
     *      }
     * })
     * @Form\Required(false)
     */
    public $initialStock = null;
}
