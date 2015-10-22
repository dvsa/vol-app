<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

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
     * @Form\Attributes({"id":"feeType"})
     * @Form\Options({
     *     "label": "fees.type",
     *     "short-label": "fees.type",
     *     "label_attributes": {"id": "label-type"},
     *     "empty_option": "Please select"
     * })
     * @Form\Type("Select")
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     */
    public $feeType = null;

    /**
     * @Form\Attributes({"id":"irfoGvPermit"})
     * @Form\Options({
     *     "label": "fees.irfoGvPermit",
     *     "short-label": "fees.irfoGvPermit",
     *     "label_attributes": {"id": "label-type"},
     *     "empty_option": "Please select"
     * })
     * @Form\Type("Select")
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     */
    public $irfoGvPermit = null;

    /**
     * @Form\Attributes({"id":"irfoPsvAuth"})
     * @Form\Options({
     *     "label": "fees.irfoPsvAuth",
     *     "short-label": "fees.irfoPsvAuth",
     *     "label_attributes": {"id": "label-type"},
     *     "empty_option": "Please select"
     * })
     * @Form\Type("Select")
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     */
    public $irfoPsvAuth = null;

    /**
     * Created date
     *
     * @Form\Options({
     *      "short-label":"fees.created_date",
     *      "label":"fees.created_date",
     *      "label_attributes": {"id": "label-createdDate"}
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required":false, "id":"createdDate"})
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $createdDate = null;

    /**
     * @Form\Options({
     *      "short-label":"fees.amount",
     *      "label":"fees.amount",
     *      "label_attributes": {"id": "label-amount"}
     * })
     * @Form\Type("Text")
     * @Form\Required(true)
     * @Form\Attributes({"required":false, "id":"amount"})
     * @Form\AllowEmpty(false)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Validator({"name": "Common\Form\Elements\Validators\Money"})
     */
    public $amount = null;
}
