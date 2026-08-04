<?php

namespace Common\Form\Model\Form\Continuation;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class Finances
{
    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Continuation\Fieldset\Finances")
     */
    public $finances;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({"label":"Continue"})
     * @Form\Type("\Laminas\Form\Element\Button")
     * @Form\Flags({"priority": -10})
     */
    public $submit;
}
