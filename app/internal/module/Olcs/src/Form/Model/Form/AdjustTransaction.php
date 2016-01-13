<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("adjust-transaction")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "action_lcfirst": true})
 */
class AdjustTransaction
{
    /**
     * @Form\Name("messages")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Messages")
     */
    public $messages;

    /**
     * @Form\Name("details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\AdjustTransactionDetails")
     */
    public $details = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\AdjustTransactionActions")
     */
    public $formActions = null;
}
