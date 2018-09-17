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
     *      "max_year_delta": "+5"
     * })
     * @Form\Attributes({"required": true})
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
     * })
     *
     * @Form\Attributes({"required": true})
     * @Form\Validator({"name": "Date", "options": {"format": "d-m-Y"}})
     * @Form\Validator({
     *      "name": "DateCompare",
     *      "options": {
     *          "compare_to":"validFrom",
     *          "operator":"gt",
     *          "compare_to_label": "Validity period start"
     *      }
     * })
     */
    public $validTo = null;

    /**
     * @Form\Name("initialStock")
     * @Form\Attributes({"id": "initialStock"})
     * @Form\Options({
     *      "label": "Quota"
     * })
     * @Form\Validator({"name":"Zend\Validator\Digits"})
     * @Form\Type("Zend\Form\Element\Number")
     * @Transfer\Validator({
     *      "name":"Zend\Validator\Between",
     *      "options": {
     *          "min": -1,
     *          "max": 9999999
     *      }
     * })
     * @Form\Required(false)
     */
    public $initialStock = null;
}
