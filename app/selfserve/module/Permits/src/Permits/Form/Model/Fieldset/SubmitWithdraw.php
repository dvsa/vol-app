<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SubmitWithdraw")
 */
class SubmitWithdraw
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"submitbutton",
     *     "value":"Withdraw application",
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $submit = null;

    /**
     * @Form\Name("SaveAndReturnButton")
     * @Form\Attributes({
     *     "class":"action--primary large return-overview",
     *     "id":"save-return-button",
     *     "value":"Return to permits dashboard",
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $save = null;
}
