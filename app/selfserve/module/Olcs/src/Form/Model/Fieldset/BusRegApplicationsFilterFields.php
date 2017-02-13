<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 * @Form\Options({
 *     "label": "Filters"
 * })
 */
class BusRegApplicationsFilterFields
{
    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "selfserve-ebsr-busreg-status-filter",
     *     "empty_option": "All",
     *     "disable_inarray_validator": false,
     *     "category": "bus_reg_status",
     * })
     * @Form\Attributes({"id":"status","placeholder":""})
     * @Form\Type("DynamicSelect")
     */
    public $status;
}
