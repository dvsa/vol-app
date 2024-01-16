<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("BusRegUpdateStatus")
 */
class BusRegUpdateStatus extends Base
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $status = null;

    /**
     * @Form\Attributes({"class":"extra-long"})
     * @Form\Options({"label":"Reason"})
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    public $reason = null;
}
