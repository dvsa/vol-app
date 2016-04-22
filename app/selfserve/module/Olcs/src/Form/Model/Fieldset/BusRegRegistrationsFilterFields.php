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
class BusRegRegistrationsFilterFields
{
    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Organisation",
     *     "empty_option": "All",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select an organisation",
     *     "service_name": "Common\Service\Data\BusRegSearchViewListDataService",
     *     "category": "organisationName"
     * })
     * @Form\Attributes({"id":"sub_type","placeholder":""})
     * @Form\Type("DynamicSelect")
     */
    public $organisationName;

    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Status",
     *     "empty_option": "All",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select status",
     *     "service_name": "Common\Service\Data\RefData",
     *     "category": "bus_reg_status"
     * })
     * @Form\Attributes({"id":"status","placeholder":""})
     * @Form\Type("DynamicSelect")
     */
    public $busRegStatusId;

    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Licence no",
     *     "empty_option": "All",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\BusRegSearchViewListDataService",
     *     "category": "licNo"
     * })
     * @Form\Attributes({"id":"status","placeholder":""})
     * @Form\Type("DynamicSelect")
     */
    public $licNo;
}
