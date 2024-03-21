<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("permits-home")
 * @Form\Attributes({"method":"get", "class": "filters  form__filter", "id": "permitHomeForm"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "bypass_auth": true})
 */
class PermitsHome
{
    /**
     * @Form\Options({
     *     "label": "<h4>Filter Applications by:</h4>",
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     }
     * })
     *
     * @Form\Type("\Common\Form\Elements\Types\Html")
     */
    public $title = null;

    /**
     * @Form\Name("status")
     * @Form\Required(false)
     * @Form\Attributes({
     *    "id" : "status",
     * })
     * @Form\Options({
     *      "label": "Status",
     *      "fieldset-attributes": {"id": "ecmt-status"},
     *      "category": "permit_application_status",
     *      "empty_option": "All",
     * })
     * @Form\Type("DynamicSelect")
     *
     *
     */
    public $status = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({
     *     "label": "Filter"
     * })
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $filter = null;
}
