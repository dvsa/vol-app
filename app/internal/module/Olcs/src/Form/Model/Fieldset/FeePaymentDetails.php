<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Fee Payment Details fieldset
 */
class FeePaymentDetails
{
    /**
     * @Form\Type("Hidden")
     */
    public $backToFee = null;

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
    public $minAmountForValidator = null;

    /**
     * @Form\Type("Hidden")
     */
    public $maxAmountForValidator = null;

    /**
     * @Form\Options({
     *     "label": "fees.payment_method",
     *     "service_name":"Olcs\Service\Data\PaymentType"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Validator("Laminas\Validator\NotEmpty")
     */
    public $paymentType = null;

    /**
     * @Form\Options({
     *      "short-label":"fees.received",
     *      "label":"fees.received",
     *      "label_attributes": {"id": "label-received"}
     * })
     * @Form\Type("Text")
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "paymentType",
     *          "context_values": {"fpm_card_offline"},
     *          "context_truth": false,
     *          "allow_empty": false,
     *          "validators": {
     *              {
     *                   "name": "Laminas\Validator\GreaterThan",
     *                   "options": {
     *                        "min": 0,
     *                        "messages": {
     *                             "notGreaterThan": "The payment amount must be greater than %min%"
     *                        }
     *                   },
     *                   "break_chain_on_failure": true
     *              },
     *              {"name": "\Common\Form\Elements\Validators\ReceivedAmount"}
     *          }
     *      }
     * )
     */
    public $received = null;

    /**
     * Receipt date, required for non-card payments
     *
     * @Form\Required(true)
     * @Form\Type("DateSelect")
     * @Form\Options({
     *      "short-label":"fees.receipt_date",
     *      "label":"fees.receipt_date",
     *      "label_attributes": {"id": "label-receiptDate"}
     * })
     * @Form\Attributes({"required":false})
     * @Form\Filter("DateSelect", options={"null_on_empty": true})
     * @Form\Validator("NotEmpty", options={"array"})
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "paymentType",
     *          "context_values": {"fpm_card_offline"},
     *          "context_truth": false,
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "NotEmpty"},
     *              {"name": "\Common\Validator\Date"},
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {"name": "\Common\Form\Elements\Validators\DateNotInFuture"}
     *          }
     *      }
     * )
     */
    public $receiptDate = null;

    /**
     * Payer name, required for non-card payments
     *
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Options({
     *      "short-label":"fees.payer",
     *      "label":"fees.payer",
     *      "label_attributes": {"id": "label-payer"}
     * })
     * @Form\Attributes({"required":false})
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "paymentType",
     *          "context_values": {"fpm_card_offline"},
     *          "context_truth": false,
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "Laminas\Validator\NotEmpty"}
     *          }
     *      }
     * })
     */
    public $payer = null;

    /**
     * Paying in slip number, required for non-card payments
     *
     * @Form\Options({
     *      "short-label":"fees.slip",
     *      "label":"fees.slip",
     *      "label_attributes": {"id": "label-slipNo"}
     * })
     * @Form\Required(true)
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Attributes({"required":false})
     * @Form\Type("Text")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "paymentType",
     *          "context_values": {"fpm_card_offline"},
     *          "context_truth": false,
     *          "allow_empty": false,
     *          "validators": {
     *              {
     *                  "name": "Digits",
     *                  "options": {
     *                      "messages": {
     *                          "digitsStringEmpty": "value is required"
     *                      }
     *                  }
     *              }
     *          }
     *      }
     * })
     */
    public $slipNo = null;

    /**
     * Cheque number, required for cheque payments only
     *
     * @Form\Required(true)
     * @Form\Options({
     *      "short-label":"fees.cheque",
     *      "label":"fees.cheque",
     *      "label_attributes": {"id": "label-chequeNo"}
     * })
     * @Form\Attributes({"required":false})
     * @Form\Type("Text")
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "paymentType",
     *          "context_values": {"fpm_cheque"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "Laminas\Validator\NotEmpty"}
     *          }
     *      }
     * })
     */
    public $chequeNo = null;

    /**
     * Cheque date, required for cheque payments
     *
     * @Form\Required(true)
     * @Form\Name("chequeDate")
     * @Form\Options({
     *      "short-label": "fees.cheque_date",
     *      "label": "fees.cheque_date",
     *      "label_attributes": {"id": "label-chequeDate"},
     *      "render_delimiters": false,
     *      "create_empty_option": true,
     *      "required": true,
     *      "max_year_delta": "+1",
     *      "min_year_delta": "-1"
     * })
     * @Form\Attributes({"required":false})
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelect", "options":{"null_on_empty":true}})
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"array"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "paymentType",
     *          "context_values": {"fpm_cheque"},
     *          "context_truth": true,
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "NotEmpty"},
     *              {"name": "\Common\Validator\Date"},
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {"name": "\Common\Form\Elements\Validators\ChequeDate"},
     *              {
     *                  "name": "\Common\Form\Elements\Validators\DateNotInFuture",
     *                  "options":{"messageTemplates":{"inFuture":"Cheque date cannot be in the future"}}}
     *          }
     *      }
     * })
     */
    public $chequeDate = null;

    /**
     * Postal Order (P.O.) number, required for P.O. payments only
     *
     * @Form\Options({
     *      "short-label":"fees.po",
     *      "label":"fees.po",
     *      "label_attributes": {"id": "label-payer"}
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
     * @Form\Type("Text")
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "paymentType",
     *          "context_values": {"fpm_po"},
     *          "allow_empty": false,
     *          "validators": {
     *              {
     *                  "name": "Digits",
     *                  "options": {
     *                      "messages": {
     *                          "digitsStringEmpty": "value is required"
     *                      }
     *                  }
     *              }
     *          }
     *      }
     * })
     */
    public $poNo = null;

    /**
     * Customer reference, required for misc payments
     *
     * @Form\Options({
     *      "short-label":"Customer reference",
     *      "label":"Customer reference",
     *      "label_attributes": {"id": "label-customer-reference"}
     * })
     * @Form\Required(true)
     * @Form\Type("Text")
     */

    public $customerReference = null;

    /**
     * Customer name, required for misc payments
     *
     * @Form\Options({
     *      "short-label":"Customer name",
     *      "label":"Customer name",
     *      "label_attributes": {"id": "label-customer-name"}
     * })
     * @Form\Required(true)
     * @Form\Type("Text")
     */
    public $customerName = null;
}
