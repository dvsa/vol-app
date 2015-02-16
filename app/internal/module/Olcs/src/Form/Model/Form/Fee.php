<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("fee")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "action_lcfirst": true})
 */
class Fee
{
    /**
     * @Form\Name("fee-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\FeeDetails")
     */
    public $feeDetails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\FeeActions")
     */
    public $formActions = null;
}
