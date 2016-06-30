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
     * @Form\Name("details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\FeePaymentDetails")
     */
    public $details = null;

    /**
     * @Form\Name("address")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Address")
     * @Form\Options({"label":"address"})
     */
    public $address = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\FeePaymentActions")
     */
    public $formActions = null;
}
