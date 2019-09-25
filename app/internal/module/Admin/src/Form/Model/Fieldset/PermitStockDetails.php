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
     * @Form\Name("irhpPermitType")
     * @Form\Attributes({"id":"irhpPermitType","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Permit Type",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Please Select",
     *     "service_name": "Common\Service\Data\IrhpPermitType",
     *     "required": true
     * })
     */
    public $irhpPermitType = null;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Name("country")
     *
     * @Form\Attributes({"id":"country","placeholder":"","class":"medium", "data-container-class":"stockCountry js-hidden"})
     * @Form\Options({
     *     "label": "Country",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Please Select",
     * })
     * @Form\Required(false)
     */
    public $country = null;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Name("applicationPathGroup")
     * @Form\Required(false)
     * @Form\Attributes({"id":"applicationPathGroup","placeholder":"","class":"medium", "data-container-class":"pathProcess js-hidden"})
     * @Form\Options({
     *     "label": "Application Path",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Please Select",
     *     "service_name": "Common\Service\Data\ApplicationPathGroup",
     *     "required": false
     * })
     */
    public $applicationPathGroup = null;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Name("businessProcess")
     * @Form\Required(true)
     * @Form\Attributes({"id":"businessProcess","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Business Process",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Please Select",
     *     "category": "app_business_process",
     *     "required": true
     * })
     */
    public $businessProcess = null;

    /**
     * @Form\Name("periodNameKey")
     * @Form\Attributes({"id": "periodNameKey"})
     * @Form\Options({
     *      "label": "Period selection translation key "
     * })
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":255}})
     * @Form\Type("Text")
     * @Form\Required(false)
     */
    public $periodNameKey = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"validFrom","placeholder":"","class":"medium", "data-container-class":"stockDates"})
     * @Form\Options({
     *     "label": "Validity Period Start",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $validFrom = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"validTo","placeholder":"","class":"medium", "data-container-class":"stockDates"})
     * @Form\Options({
     *     "label": "Validity Period End",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
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
