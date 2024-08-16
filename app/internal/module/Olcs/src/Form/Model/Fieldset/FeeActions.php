<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"govuk-button-group"})
 * @Form\Name("fee-actions")
 */
class FeeActions
{
    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button",
     *     "id": "pay",
     * })
     * @Form\Options({"label": "Pay"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $pay = null;

    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button",
     *     "id": "recommend",
     * })
     * @Form\Options({"label": "Recommend waive"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $recommend = null;

    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button",
     *     "id": "approve",
     * })
     * @Form\Options({"label": "Approve waive"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $approve = null;

    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button",
     *     "id": "reject",
     * })
     * @Form\Options({"label": "Reject waive"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $reject = null;

    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button",
     *     "id": "refund",
     * })
     * @Form\Options({"label": "Refund fee"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $refund = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary",
     *     "id":"cancel",
     * })
     * @Form\Options({"label": "Back"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;
}
