<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * Fee Payment Details fieldset
 */
class FeePaymentDetails
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Type("Common\Form\Elements\Types\Html")
     * @Form\Options({
     *     "label": "fees_max_amount",
     * })
     */
    public $maxAmount = null;

    /**
     * @Form\Options({
     *     "label": "fees_payment_method",
     *     "service_name":"Olcs\Service\Data\PaymentType"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     */
    public $paymentType = null;

    /**
     * @Form\Options({"label":"fees_received"})
     * @Form\Type("Text")
     * @Form\Validator({
     *     "name": "Zend\Validator\GreaterThan",
     *     "options": {"min": 0}
     * })
     */
    public $received = null;
}
