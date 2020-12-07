<?php

namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Submit")
 */
class SubmitApplication
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"submitbutton",
     *     "value":"permits.button.submit-application",
     * })
     * @Form\Type("Laminas\Form\Element\Submit")
     */
    public $submit = null;
}
