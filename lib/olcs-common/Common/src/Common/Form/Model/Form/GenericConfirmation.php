<?php

namespace Common\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("generic-confirmation")
 * @Form\Attributes({"method":"post", "class":"js-modal-alert"})
 * @Form\Type("Common\Form\GenericConfirmation")
 */
class GenericConfirmation
{
    /**
     * @Form\Name("messages")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Messages")
     */
    public $messages;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\ConfirmButtons")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
