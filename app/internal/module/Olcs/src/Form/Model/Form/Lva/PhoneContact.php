<?php

namespace Olcs\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("lva-phone-contact")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class PhoneContact
{
    /**
     * @Form\Name("details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\Lva\PhoneContact")
     */
    public $details = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormCrudActions")
     */
    public $formActions = null;
}
