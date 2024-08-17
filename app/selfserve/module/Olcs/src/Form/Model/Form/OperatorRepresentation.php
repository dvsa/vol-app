<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("OperatorRepresentation")
 * @Form\Attributes({"method":"post","label":"user-registration.form.label"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "label": "user-registration.form.label"})
 */
class OperatorRepresentation
{
    /**
     * @Form\Name("fields")
     * @Form\Options({"label":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OperatorRepresentation")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ContinueButton")
     */
    public $formActions = null;
}
