<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("ConfirmAbortLetterGeneration")
 * @Form\Options({"label":""})
 * @Form\Attributes({
 *     "method":"post",
 *     "class":"js-modal-alert",
 *     "data-close-trigger":"#no"
 * })
 */
class ConfirmAbortLetterGeneration
{
    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\YesNoFormActions")
     */
    public $formActions = null;
}
