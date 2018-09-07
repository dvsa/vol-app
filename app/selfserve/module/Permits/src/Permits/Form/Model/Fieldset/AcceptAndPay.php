<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("AcceptAndPay")
 */
class AcceptAndPay
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"submit-accept-button",
     *     "value":"permits.page.accept.and.pay",
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $submit = null;

    /**
     * @Form\Name("DeclineButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"submit-decline-button",
     *     "value":"permits.page.decline.permits",
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $decline = null;
}