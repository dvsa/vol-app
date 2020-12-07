<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("WithdrawButton")
 */
class WithdrawButton
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"withdrawbutton",
     *     "value":"permits.form.withdraw_application.button",
     * })
     * @Form\Type("Laminas\Form\Element\Submit")
     */
    public $withdraw = null;
}
