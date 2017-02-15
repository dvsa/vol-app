<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("Cpid-filter")
 * @Form\Attributes({"method":"get", "class":"form__filter filters"})
 * @Form\Type("Common\Form\Form")
 */
class CpidFilter
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "internal-licence-fees-status",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Not set",
     *     "category": "operator_cpid"
     * })
     * @Form\Type("DynamicSelect")
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
