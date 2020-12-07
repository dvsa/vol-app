<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SaveAndContinueOrCancelApplication")
 */
class SaveAndContinueOrCancelApplication
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "value":"Save and continue",
     * })
     * @Form\Type("Laminas\Form\Element\Submit")
     */
    public $submit = null;

    /**
     * @Form\Name("CancelButton")
     * @Form\Attributes({
     *     "role":"link",
     *     "value":"permits.form.cancel_application.button",
     * })
     * @Form\Type("Laminas\Form\Element\Submit")
     */
    public $cancel = null;
}
