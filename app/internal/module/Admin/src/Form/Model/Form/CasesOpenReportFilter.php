<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("cases-open-report-filter")
 * @Form\Attributes({
 *     "method": "GET",
 *     "class": "filters form__filter",
 * })
 * @Form\Type("Common\Form\Form")
 * @Form\Options({
 *     "prefer_form_input_filter": true,
 * })
 */
class CasesOpenReportFilter
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Traffic areas",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\TrafficArea",
     *     "other_option": false,
     *     "extra_option": {
     *          "": "All",
     *          "OTHER": "Other",
     *     }
     * })
     * @Form\Type("DynamicSelect")
     */
    public $trafficArea = null;

    /**
     * @Form\Options({
     *     "label": "Case type",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\RefData",
     *     "other_option": false,
     *     "extra_option": {"": "All"},
     *     "context": "case_type",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $caseType = null;

    /**
     * @Form\Options({
     *     "label": "Licence status",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\RefData",
     *     "other_option": false,
     *     "extra_option": {"": "All"},
     *     "context": "lic_status",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $licenceStatus = null;

    /**
     * @Form\Options({
     *     "label": "Application status",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\RefData",
     *     "other_option": false,
     *     "extra_option": {"": "All"},
     *     "context": "app_status",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $applicationStatus = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary"})
     * @Form\Options({
     *     "label": "filter-button"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $filter = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $limit = null;
}
