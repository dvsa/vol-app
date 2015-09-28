<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("Publish")
 * @Form\Options({"label":""})
 * @Form\Attributes({"method":"post"})
 * @Form\Type(\Olcs\Form\Message::class)
 */
class Message
{
    /**
     * @Form\Name("messages")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Messages")
     */
    public $messages;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OkCancelFormActions")
     */
    public $formActions = null;
}
