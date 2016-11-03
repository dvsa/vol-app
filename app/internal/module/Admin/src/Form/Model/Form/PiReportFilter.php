<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("pi-report-filter")
 * @Form\Attributes({"method":"get","class":"filters form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class PiReportFilter
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "PI date from"
     * })
     * @Form\Required(true)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     */
    public $startDate = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "PI date to"
     * })
     * @Form\Required(true)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({
     *      "name": "DateCompare",
     *      "options": {
     *          "has_time": false,
     *          "compare_to":"startDate",
     *          "operator":"gte",
     *          "compare_to_label":"PI date from"
     *      }
     * })
     */
    public $endDate = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Venue",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\TrafficArea",
     *     "other_option": false,
     *     "extra_option": {"other": "Other Venue"},
     * })
     * @Form\Type("DynamicMultiCheckbox")
     */
    public $trafficAreas = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary"})
     * @Form\Options({
     *     "label": "filter-button"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $filter = null;
}
