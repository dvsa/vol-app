<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("bus-reg-list")
 * @Form\Attributes({"method":"get", "class":"form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "label":"Bus Registration List", "bypass_auth": true})
 */
class BusRegList
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "internal-licence-bus.data.status",
     *     "disable_inarray_validator": false,
     *     "category": "bus_reg_status",
     *     "empty_option": "All"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $status = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary"})
     * @Form\Options({
     *     "label": "internal-licence-bus.submit.filter"
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
