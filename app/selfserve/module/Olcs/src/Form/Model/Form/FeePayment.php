<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("fee-payment")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class FeePayment
{
    /**
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $amount = null;


    /**
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\FeeStoredCards")
     */
    public $storedCards = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\FeePaymentActions")
     */
    public $formActions = null;
}
