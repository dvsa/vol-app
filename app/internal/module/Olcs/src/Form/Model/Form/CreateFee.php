<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("fee")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "action_lcfirst": true})
 */
class CreateFee
{
    /**
     * @Form\Name("fee-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CreateFeeDetails")
     */
    public $feeDetails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
