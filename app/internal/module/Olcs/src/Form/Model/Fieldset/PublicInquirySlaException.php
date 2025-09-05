<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 * @Form\Options({"label":""})
 */
class PublicInquirySlaException extends Base
{
    /**
     * @Form\Attributes({"id":"sla","placeholder":""})
     * @Form\Options({
     *     "label": "SLA",
     *     "service_name": "Olcs\Service\Data\Category",
     *     "context": {"isCaseSlaException": "Y" },
     *     "empty_option": "Please Select"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $sla = null;

    /**
     * @Form\Attributes({"id":"subCategory","placeholder":""})
     * @Form\Options({
     *     "label": "Reason for exception",
     *     "service_name": "Olcs\Service\Data\SubCategory",
     *     "context": {"isCaseSlaException": "Y" },
     *     "empty_option": "Please Select"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $reasonForException = null;
}
