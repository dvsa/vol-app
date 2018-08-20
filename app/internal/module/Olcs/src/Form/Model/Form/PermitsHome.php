<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

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
     *     "label": "<h4>Filter by</h4>",
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     }
     * })
     *
     * @Form\Type("\Common\Form\Elements\Types\Html")
     */
    public $title = null;

    /**
     * @Form\Options({
     *     "label": "Status",
     *     "value_options": {
     *          "all":"All",
     *          "ecmt_permit_nys":"Not Yet Submitted",
     *          "ecmt_permit_uc":"Under Consideration"
     *     },
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $status = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary"})
     * @Form\Options({
     *     "label": "permits-home.submit.filter"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $filter = null;
}
