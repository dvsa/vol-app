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

    /**
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
     * @Form\Options({"label":"fees.receipt_date"})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "paymentType",
     *          "context_values": {"fpm_card_offline"},
     *          "context_truth": false,
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {"name": "\Common\Form\Elements\Validators\DateNotInFuture"}
     *          }
     *      }
     * })
     */
    public $receiptDate = null;

    /**
     * @Form\Options({"label":"fees.payer"})
     * @Form\Type("Text")
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     */
    public $payer = null;

    /**
     * Paying in slip number
     * @Form\Options({"label":"fees.slip"})
     * @Form\Type("Text")
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     */
    public $slipNo = null;

    /**
     * Cheque number
     * @Form\Options({"label":"fees.cheque"})
     * @Form\Type("Text")
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     */
    public $chequeNo = null;

    /**
     * Postal Order (P.O.) number
     * @Form\Options({"label":"fees.po"})
     * @Form\Type("Text")
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     */
    public $poNo = null;
}
