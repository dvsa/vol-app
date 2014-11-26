<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

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
     * @Form\Filter({"name": "Int"})
     * @Form\Options({
     *     "label": "fees_payment_method",
     *     "category":"fee_pay_method"
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
