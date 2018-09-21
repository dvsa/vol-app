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
     * @Form\Required(true)
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
     * @Form\Options({
     *     "label": "Validity Period End",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Validator({
     *      "name": "DateCompare",
     *      "options": {
     *          "has_time": false,
     *          "allow_empty": true,
     *          "compare_to":"validFrom",
     *          "operator":"gt",
     *          "compare_to_label":"validFrom"
     *      }
     * })
     * @Form\Validator({
     *      "name": "Dvsa\Olcs\Transfer\Validators\DateInFuture",
     *      "options": {
     *          "include_today": true,
     *          "use_time": false,
     *          "allow_empty": true,
     *          "error-message": "Validity Period End must be later than Validity Period Start"
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
