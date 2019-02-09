<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SubmitAndPay")
 */
class SubmitAndPay
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"govuk-button",
     *     "value":"permits.button.submit-and-pay",
     *     "role":"link"
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $submit = null;
}
