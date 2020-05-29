<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("irhp-permit-filter")
 * @Form\Attributes({"method":"get", "class":"filters form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class IrhpPermitFilter
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "permits.irhp.permit.filter.type",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\IrhpPermitType",
     *     "extra_option": {"": "All"},
     *     "exclude":{"6", "7"}
     * })
     * @Form\Type("DynamicSelect")
     */
    public $irhpPermitType;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "permits.irhp.permit.filter.country",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\Country",
     *     "extra_option": {"": "All"},
     * })
     * @Form\Type("DynamicSelect")
     */
    public $country;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "permits.irhp.permit.filter.status",
     *     "disable_inarray_validator": false,
     *     "category": "irhp_permit_status",
     *     "empty_option": "All"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $status;

    /**
     * @Form\Attributes({"value":"10"})
     * @Form\Type("Hidden")
     */
    public $limit;

    /**
     * @Form\Attributes({"value":"1"})
     * @Form\Type("Hidden")
     */
    public $page;
}
