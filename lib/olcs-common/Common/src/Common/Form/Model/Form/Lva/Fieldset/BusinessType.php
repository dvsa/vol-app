<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Business type fieldset
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BusinessType
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "application_your-business_business-type.data.type",
     *     "label_attributes": {"class": "form-control form-control--radio"},
     *     "disable_inarray_validator": false,
     *     "category": "org_type",
     *     "exclude": {"org_t_ir"}
     * })
     * @Form\Type("DynamicRadio")
     */
    public $type;
}
