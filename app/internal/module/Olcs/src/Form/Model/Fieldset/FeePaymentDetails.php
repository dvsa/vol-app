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
     * @Form\Type("Hidden")
     */
    public $feeAmountForValidator = null;

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
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "paymentType",
     *          "context_values": {"fpm_card_offline"},
     *          "context_truth": false,
     *          "allow_empty": false,
     *          "validators": {
     *              {
     *                  "name": "\Common\Form\Elements\Validators\FeeExactAmountValidator",
     *                  "options": {"strict": false, "token": "feeAmountForValidator"}
     *              }
     *          }
     *      }
     * })
     */
    public $received = null;

    /**
     * Receipt date, required for non-card payments
     *
     * @Form\Options({"label":"fees.receipt_date"})
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
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
     * Payer name, required for non-card payments
     *
     * @Form\Options({"label":"fees.payer"})
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("Text")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "paymentType",
     *          "context_values": {"fpm_card_offline"},
     *          "context_truth": false,
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "Zend\Validator\NotEmpty"}
     *          }
     *      }
     * })
     */
    public $payer = null;

    /**
     * Paying in slip number, required for non-card payments
     *
     * @Form\Options({"label":"fees.slip"})
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("Text")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "paymentType",
     *          "context_values": {"fpm_card_offline"},
     *          "context_truth": false,
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "Zend\Validator\NotEmpty"}
     *          }
     *      }
     * })
     */
    public $slipNo = null;

    /**
     * Cheque number, required for cheque payments only
     *
     * @Form\Options({"label":"fees.cheque"})
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("Text")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "paymentType",
     *          "context_values": {"fpm_cheque"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "Zend\Validator\NotEmpty"}
     *          }
     *      }
     * })
     */
    public $chequeNo = null;

    /**
     * Postal Order (P.O.) number, required for P.O. payments only
     *
     * @Form\Options({"label":"fees.po"})
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("Text")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "paymentType",
     *          "context_values": {"fpm_po"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "Zend\Validator\NotEmpty"}
     *          }
     *      }
     * })
     */
    public $poNo = null;
}
