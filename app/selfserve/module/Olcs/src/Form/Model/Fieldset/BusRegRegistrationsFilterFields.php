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
     *     "category": "organisation"
     * })
     * @Form\Attributes({"id":"organisation","placeholder":""})
     * @Form\Type("DynamicSelect")
     */
    public $organisationId;

    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Registration status",
     *     "empty_option": "All",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select status",
     *     "service_name": "Common\Service\Data\BusRegSearchViewListDataService",
     *     "category": "busRegStatus"
     * })
     * @Form\Attributes({"id":"busRegStatus","placeholder":""})
     * @Form\Type("DynamicSelect")
     */
    public $busRegStatus;

    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Licence no",
     *     "empty_option": "All",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\BusRegSearchViewListDataService",
     *     "category": "licence"
     * })
     * @Form\Attributes({"id":"licId","placeholder":""})
     * @Form\Type("DynamicSelect")
     */
    public $licId;
}
