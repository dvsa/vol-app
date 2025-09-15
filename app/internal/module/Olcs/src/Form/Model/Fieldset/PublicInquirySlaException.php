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
     * @Form\Attributes({"type":"hidden"})
     * @Form\Type("Hidden")
     */
    public $case = null;

    /**
     * @Form\Attributes({"id":"slaException","placeholder":"","required":"required"})
     * @Form\Options({
     *     "label": "SLA Exception",
     *     "service_name": "Olcs\Service\Data\SlaException",
     *     "empty_option": "Please Select",
     *     "use_groups": true,
     *     "hint": "Please note, choose carefully; once an SLA exception is saved, you will not be able to edit or delete it.",
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(true)
     * @Form\Validator("Laminas\Validator\NotEmpty")
     */
    public $slaException = null;
}
