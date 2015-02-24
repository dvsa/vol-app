<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("generic-confirmation")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class CreateVariation
{
    /**
     * @Form\Name("messages")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Messages")
     */
    public $messages;

    /**
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CreateVariation")
     */
    public $data;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CreateConfirmationButtons")
     * @Form\Attributes({"class":"actions-container"})
     */
    public $formActions = null;
}
