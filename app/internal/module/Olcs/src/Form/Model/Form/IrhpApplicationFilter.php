<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("irhp-application-filter")
 * @Form\Attributes({"method":"get", "class":"filters form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class IrhpApplicationFilter
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "permits.irhp.application.filter.status",
     *     "disable_inarray_validator": false,
     *     "category": "permit_application_status",
     *     "empty_option": "All"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $status;
}
