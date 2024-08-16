<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * IRFO Stock Control form.
 */
class IrfoStockControl
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Country",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\IrfoCountry"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $irfoCountry = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Year",
     *     "empty_option": "Please Select",
     *     "min_year_delta": "-40",
     *     "max_year_delta": "+5"
     * })
     * @Form\Type("YearSelect")
     */
    public $validForYear = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Status",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "irfo_permit_stock_status"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $status = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"small"})
     * @Form\Options({"label": "Serial No - Start"})
     * @Form\Type("Text")
     * @Form\Validator("Digits")
     * @Form\Validator({
     *     "name":"GreaterThan",
     *     "options": {
     *         "min":"0",
     *     }
     * })
     */
    public $serialNoStart = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"small"})
     * @Form\Options({"label": "Serial No - End"})
     * @Form\Type("Text")
     * @Form\Validator("Digits")
     * @Form\Validator({
     *     "name":"GreaterThan",
     *     "options": {
     *         "min":"0",
     *     }
     * })
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "serialNoStart",
     *          "context_values": {""},
     *          "context_truth": false,
     *          "allow_empty" : true,
     *          "validators": {
     *              {
     *                  "name": "NumberCompare",
     *                  "options": {
     *                      "compare_to":"serialNoStart",
     *                      "operator":"gte",
     *                      "compare_to_label":"Serial No - Start",
     *                      "max_diff":"100",
     *                  }
     *              }
     *          }
     *      }
     * })
     */
    public $serialNoEnd = null;
}
