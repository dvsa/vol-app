<?php

namespace Common\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("internal-generic-delete-confirmation")
 * @Form\Attributes({"method":"post", "class":"js-modal-alert"})
 * @Form\Type("Common\Form\Form")
 */
class LastTransportManagerDeleteConfirmation extends GenericDeleteConfirmation
{
    /**
     * @Form\Name("YesNoRadio")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\YesNoRadio")
     */
    public $yesNoRadio;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\DeleteConfirmButtons")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
