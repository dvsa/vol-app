<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Submit")
 */
class Submit
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"submitbutton",
     *     "value":"Save and continue",
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
