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
     *     "label": "Registration type",
     *     "empty_option": "All",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a registration type",
     *     "service_name": "Common\Service\Data\EbsrSubTypeListDataService",
     *     "category": "ebsr_sub_type"
     * })
     * @Form\Attributes({"id":"sub_type","placeholder":""})
     * @Form\Type("DynamicSelect")
     */
    public $subType;

    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Status",
     *     "empty_option": "All",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\RefData",
     *     "category": "ebsr_sub_status"
     * })
     * @Form\Attributes({"id":"status","placeholder":""})
     * @Form\Type("DynamicSelect")
     */
    public $status;
}
