<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("ExistingOperatorLicence")
 * @Form\Attributes({"method":"post","label":"user-registration.form.label"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class ExistingOperatorLicence
{
    /**
     * @Form\Name("fields")
     * @Form\Options({"label": "user-registration.field.existing-operator-licence.label",
     *      "hint": "user-registration.field.existing-operator-licence.hint"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ExistingOperatorLicence")
     *
     */
    public $fields = null;


    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ContinueButton")
     */
    public $formActions = null;
}
