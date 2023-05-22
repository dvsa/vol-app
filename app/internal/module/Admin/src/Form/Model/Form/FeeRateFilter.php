<?php

namespace Admin\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("FeeRateFilter")
 * @Form\Attributes({"method":"get", "class": "filters  form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "bypass_auth": true})
 */
class FeeRateFilter
{
    /**
     * @Form\Options({
     *     "label": "Goods or PSV",
     *     "disable_inarray_validator": false,
     *     "value_options": {
     *          "" : "All",
     *          "lcat_gv" : "Goods",
     *          "lcat_psv" : "PSV",
     *          "lcat_permit" : "Bus Permits",
     *     },
     * })
     * @Form\Type("Select")
     */
    public $goodsOrPsv = null;

    /**
     * @Form\Options({
     *     "label": "Fee Type",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\FeeType",
     *     "empty_option": "All",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $feeType = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({
     *     "label": "documents-home.submit.filter"
     * })
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $filter = null;
}
