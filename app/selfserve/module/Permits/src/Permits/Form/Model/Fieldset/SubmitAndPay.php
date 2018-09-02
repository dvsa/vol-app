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
     *     "class":"action--primary large",
     *     "id":"submit-accept-button",
     *     "value":"permits.button.submit-and-pay",
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $submit = null;
}