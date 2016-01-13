<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("Confirm")
 * @Form\Options({"label":""})
 * @Form\Attributes({"method":"post", "class":"js-modal-alert"})
 */
class Confirm
{
    /**
     * @Form\Type("Hidden")
     */
    public $custom = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ConfirmFormActions")
     */
    public $formActions = null;
}
