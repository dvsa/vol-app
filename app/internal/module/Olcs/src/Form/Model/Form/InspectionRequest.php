<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("inspection-request")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class InspectionRequest
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\InspectionRequestDetails")
     */
    public $data = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\SaveButtons")
     */
    public $formActions = null;
}
