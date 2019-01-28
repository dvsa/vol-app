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
     *     "type":"submit",
     *     "class":"govuk-button",
     *     "role":"link"
     * })
     * @Form\Options({
     *     "label":"permits.button.confirm-and-continue",
     * })
     * @Form\Type("Zend\Form\Element\Button")
     */
    public $submit = null;

    /**
     * @Form\Name("SaveAndReturnButton")
     * @Form\Attributes({
     *     "id":"save-return-button",
     *     "value":"permits.button.save-return-to-overview",
     *     "role":"link"
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $save = null;
}
