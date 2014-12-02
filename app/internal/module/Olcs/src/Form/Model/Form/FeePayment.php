<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("fee_payment")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class FeePayment
{
    /**
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\FeePaymentDetails")
     */
    public $details = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\FeePaymentActions")
     * @Form\Attributes({"class":"actions-container"})
     */
    public $formActions = null;
}
