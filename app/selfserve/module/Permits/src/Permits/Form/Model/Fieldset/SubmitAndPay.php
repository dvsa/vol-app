<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SubmitAndPay")
 * @Form\Attributes({
 *     "class":"govuk-button-group",
 * })
 */
class SubmitAndPay
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"govuk-button",
     *     "data-module": "govuk-button",
     *     "role":"button",
     *     "type":"submit",
     * })
     * @Form\Options({
     *     "label":"permits.button.submit-and-pay",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;
}
