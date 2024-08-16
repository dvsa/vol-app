<?php

namespace Olcs\Form\Model\Form\Filter;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("operator-application-filters")
 * @Form\Attributes({
 *     "method":"get",
 *     "class": "filters form__filter"
 * })
 * @Form\Type("Common\Form\Form")
 * @Form\Options({
 *     "prefer_form_input_filter": true,
 *     "bypass_auth": true
 * })
 */
class OperatorApplication
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Status",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\ApplicationStatus",
     *     "other_option": false,
     *     "extra_option": {"": "All"},
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
     *     "label": "tasks.submit.filter",
     *     "empty_option": "ch_alert_reason.all"
     * })
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $filter = null;
}
