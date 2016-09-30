<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("fee-payment-actions")
 * @Form\Attributes({"class":"actions-container"})
 */
class FeePaymentActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary action--external large"})
     * @Form\Options({"label": "pay-now"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $pay = null;

    /**
     * @Form\Attributes({"id":"cancel","type":"submit","class":"action--secondary large"})
     * @Form\Options({"label": "cancel.button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $customCancel = null;
}
