<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

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
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ConfirmFormActions")
     */
    public $formActions = null;
}
