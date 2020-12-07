<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

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
     *     "class":"action--primary large",
     *     "role":"button"
     * })
     * @Form\Options({
     *     "label":"permits.button.confirm-and-continue",
     * })
     * @Form\Type("Laminas\Form\Element\Button")
     */
    public $submit = null;

    /**
     * @Form\Name("SaveAndReturnButton")
     * @Form\Attributes({
     *     "id":"save-return-button",
     *     "value":"permits.button.save-return-to-overview",
     *     "role":"link"
     * })
     * @Form\Type("Laminas\Form\Element\Submit")
     */
    public $save = null;
}
