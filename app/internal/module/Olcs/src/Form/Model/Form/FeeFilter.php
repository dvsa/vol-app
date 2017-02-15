<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("fee-filter")
 * @Form\Attributes({"method":"get", "class":"form__filter filters"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "bypass_auth": true})
 */
class FeeFilter
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "internal-licence-fees-status",
     *     "value_options": {
     *          "current":"Current",
     *          "historical":"Historic",
     *          "all":"All"
     *     },
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $status = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary"})
     * @Form\Options({
     *     "label": "tasks.submit.filter"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $filter = null;
}
