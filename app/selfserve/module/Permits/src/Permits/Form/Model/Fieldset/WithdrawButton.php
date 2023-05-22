<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("WithdrawButton")
 * @Form\Attributes({
 *     "class":"govuk-button-group",
 * })
 */
class WithdrawButton
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"govuk-button",
     *     "data-module": "govuk-button",
     *     "id":"withdrawbutton",
     *     "type":"submit",
     * })
     * @Form\Options({
     *     "label":"permits.form.withdraw_application.button",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $withdraw = null;
}
