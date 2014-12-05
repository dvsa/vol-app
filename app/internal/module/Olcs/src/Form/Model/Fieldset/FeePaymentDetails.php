<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * Fee Payment Details fieldset
 */
class FeePaymentDetails
{
    /**
     * @Form\Type("Common\Form\Elements\Types\Html")
     * @Form\Options({
     *     "label": "fees.max_amount",
     * })
     */
    public $maxAmount = null;

    /**
     * @Form\Options({
     *     "label": "fees.payment_method",
     *     "service_name":"Olcs\Service\Data\PaymentType"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     */
    public $paymentType = null;

    /**
     * @Form\Options({"label":"fees.received"})
     * @Form\Type("Text")
     * @Form\Validator({
     *     "name": "Zend\Validator\GreaterThan",
     *     "options": {"min": 0}
     * })
     */
    public $received = null;
}
