<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("transport-manager-details")
 */
class InterimStatus
{
    /**
     * @Form\Options({
     *     "label": "internal.interim.form.interim_status",
     *     "category": "interim_status",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $status = null;
}
