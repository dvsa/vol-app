<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("companies-house-alert-filters")
 * @Form\Attributes({"method":"get", "class": "filters form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "bypass_auth": true})
 */
class CompaniesHouseAlertFilters
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "companies-house-alert.filters.include-closed",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\Checkbox")
     */
    public $includeClosed = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "companies-house-alert.filters.type-of-change",
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $typeOfChange = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary"})
     * @Form\Options({
     *     "label": "tasks.submit.filter",
     *     "empty_option": "ch_alert_reason.all"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $filter = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $sort = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $order = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $limit = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $page = null;
}
