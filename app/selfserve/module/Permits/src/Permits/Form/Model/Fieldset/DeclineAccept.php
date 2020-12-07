<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SubmitAccept")
 */
class DeclineAccept
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"submit-accept-button",
     *     "value":"permits.button.accept-and-continue",
     * })
     * @Form\Type("Laminas\Form\Element\Submit")
     */
    public $submit = null;
}
