<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("WithdrawButton")
 */
class WithdrawButton
{
    /**
     * @Form\Name("WithdrawButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"withdrawbutton",
     *     "value":"permits.form.withdraw_application.button",
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $withdraw = null;
}
