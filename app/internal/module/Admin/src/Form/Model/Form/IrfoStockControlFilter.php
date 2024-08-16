<?php

namespace Admin\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("irfo-stock-control-filter")
 * @Form\Attributes({"method":"get","class":"filters form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class IrfoStockControlFilter
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Country",
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
     *     "empty_option": "All",
     *     "disable_inarray_validator": false,
     *     "category": "irfo_permit_stock_status"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $status = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({
     *     "label": "filter-button"
     * })
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $filter = null;
}
