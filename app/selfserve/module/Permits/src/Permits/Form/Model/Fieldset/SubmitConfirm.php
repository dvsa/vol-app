<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SubmitConfirm")
 */
class SubmitConfirm
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"submit-accept-button",
     *     "value":"permits.button.confirm-and-continue",
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $submit = null;

    /**
     * @Form\Name("SaveAndReturnButton")
     * @Form\Attributes({
     *     "class":"action--primary large return-overview",
     *     "id":"save-return-button",
     *     "value":"Save and return to overview",
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $save = null;
}
