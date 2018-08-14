<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("permits-home")
 * @Form\Attributes({"method":"get", "class": "filters  form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "bypass_auth": true})
 */
class PermitsHome
{

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Status",
     *     "value_options": {
     *          "all":"All",
     *          "ecmt_permit_nys":"Not Yet Submitted",
     *          "emct_permit_uc":"Under Consideration"
     *     },
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $status = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary"})
     * @Form\Options({
     *     "label": "documents-home.submit.filter"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $filter = null;
}
