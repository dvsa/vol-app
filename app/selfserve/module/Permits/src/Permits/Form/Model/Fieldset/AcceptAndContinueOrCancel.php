<?php

namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("AcceptAndContinueOrCancel")
 */
class AcceptAndContinueOrCancel
{
    /**
     * @Form\Name("AcceptAndContinueButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "value":"permits.button.accept-and-continue",
     * })
     * @Form\Type("Laminas\Form\Element\Submit")
     */
    public $submit = null;

    /**
     * @Form\Name("CancelButton")
     * @Form\Attributes({
     *     "value":"permits.button.cancel-return-to-overview",
     *     "role":"link"
     * })
     * @Form\Type("Laminas\Form\Element\Submit")
     */
    public $save = null;
}
