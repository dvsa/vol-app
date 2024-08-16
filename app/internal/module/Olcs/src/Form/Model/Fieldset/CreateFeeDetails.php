<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":""})
 * @Form\Name("fee-details")
 */
class CreateFeeDetails
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
     * Created date
     *
     * @Form\Options({
     *      "short-label":"fees.created_date",
     *      "label":"fees.created_date",
     *      "label_attributes": {"id": "label-createdDate"},
     *      "create_empty_option": true,
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required":false, "id":"createdDate"})
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $createdDate = null;

    /**
     * @Form\Attributes({"id":"feeType"})
     * @Form\Options({
     *     "label": "fees.type",
     *     "short-label": "fees.type",
     *     "label_attributes": {"id": "label-type"},
     *     "empty_option": "Please select"
     * })
     * @Form\Type("Select")
     * @Form\Validator({"name": "Laminas\Validator\NotEmpty"})
     */
    public $feeType = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"irfoGvPermit","required":false})
     * @Form\Options({
     *     "label": "fees.irfoGvPermit",
     *     "short-label": "fees.irfoGvPermit",
     *     "label_attributes": {"id": "label-type"},
     *     "empty_option": "Please select"
     * })
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Type("Select")
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "irfoPsvAuth",
     *          "context_values": {""},
     *          "context_truth": true,
     *          "allow_empty" : false,
     *          "validators": {
     *              {
     *                  "name": "Laminas\Validator\NotEmpty",
     *                  "options": {"messages": {"isEmpty": "internal.create-fee.irfo-required"}}
     *              }
     *          }
     *      }
     * })
     */
    public $irfoGvPermit = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"irfoPsvAuth","required":false})
     * @Form\Options({
     *     "label": "fees.irfoPsvAuth",
     *     "short-label": "fees.irfoPsvAuth",
     *     "label_attributes": {"id": "label-type"},
     *     "empty_option": "Please select"
     * })
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Type("Select")
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "irfoGvPermit",
     *          "context_values": {""},
     *          "context_truth": true,
     *          "allow_empty" : false,
     *          "validators": {
     *              {
     *                  "name": "Laminas\Validator\NotEmpty",
     *                  "options": {"messages": {"isEmpty": "internal.create-fee.irfo-required"}}
     *              }
     *          }
     *      }
     * })
     */
    public $irfoPsvAuth = null;

    /**
     * @Form\Options({"label":"Quantity"})
     * @Form\Type("Text")
     * @Form\Attributes({"id":"quantity"})
     * @Form\Validator(
     *  {
     *      "name": "Laminas\Validator\GreaterThan",
     *      "options": {
     *          "min": 1,
     *          "inclusive": true,
     *          "messages": {"notGreaterThanInclusive": "You must enter %min% or above"}
     *      }
     *  }
     * )
     */
    public $quantity = null;

    /**
     * @Form\Options({
     *      "short-label":"fees.amount",
     *      "label":"fees.amount",
     *      "label_attributes": {"id": "label-amount"}
     * })
     * @Form\Type("Text")
     * @Form\Required(true)
     * @Form\Attributes({"required":false, "id":"amount"})
     * @Form\Validator({"name": "Dvsa\Olcs\Transfer\Validators\Money"})
     */
    public $amount = null;

    /**
     * @Form\Options({
     *      "short-label":"fees.vat_rate",
     *      "label":"fees.vat_rate",
     *      "label_attributes": {"id": "label-vat_rate"}
     * })
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Attributes({"disabled":"disabled", "id":"vat-rate"})
     */
    public $vatRate = null;
}
