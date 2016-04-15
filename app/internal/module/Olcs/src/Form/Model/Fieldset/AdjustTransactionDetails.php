<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":""})
 * @Form\Name("details")
 */
class AdjustTransactionDetails
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
     * @Form\Type("Hidden")
     */
    public $paymentType = null;

    /**
     * @Form\Options({"label": "fees.payment_method"})
     * @Form\Type("Common\Form\Elements\Types\Readonly")
     */
    public $paymentMethod = null;

    /**
     * @Form\Options({
     *      "short-label":"fees.received",
     *      "label":"fees.received",
     *      "label_attributes": {"id": "label-received"}
     * })
     * @Form\Type("Text")
     * @Form\Validator({"name": "Dvsa\Olcs\Transfer\Validators\Money"})
     */
    public $received = null;

    /**
     * Payer name, required for non-card payments
     *
     * @Form\Options({
     *      "short-label":"fees.payer",
     *      "label":"fees.payer",
     *      "label_attributes": {"id": "label-payer"}
     * })
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
     * @Form\Options({
     *      "short-label":"fees.slip",
     *      "label":"fees.slip",
     *      "label_attributes": {"id": "label-slipNo"}
     * })
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
     * @Form\Options({
     *      "short-label":"fees.cheque",
     *      "label":"fees.cheque",
     *      "label_attributes": {"id": "label-chequeNo"}
     * })
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
     * Cheque date, required for cheque payments
     *
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
     * @Form\Required(true)
     * @Form\Attributes({"required":false})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
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
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("Text")
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
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label":"Reason for the adjustment",
     *     "short-label":"Reason",
     *     "label_attributes": {"id": "label-reason"}
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\Textarea")
     * @Form\Validator({
     *     "name": "Zend\Validator\NotEmpty",
     *     "options": {"messages": {"isEmpty": "You must enter a reason"}}
     * })
     */
    public $reason = null;
}
