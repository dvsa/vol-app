<?php

namespace Olcs\Form\Model\Form\Licence\Surrender;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("LicenceStatusDecisionMessages")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class Confirmation
{
    /**
     * @Form\Name("messages")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Messages")
     */
    public $messages;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\LicenceStatusDecisionMessagesFormActions")
     */
    public $formActions = null;
}
