<?php

namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SaveAndReturnOnly")
 */
class SaveAndReturnOnly
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"saveandreturnbutton",
     *     "value":"save.button",
     * })
     * @Form\Type("Laminas\Form\Element\Submit")
     */
    public $submit = null;
}
