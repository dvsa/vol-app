<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("ExistingOperatorLicence")
 * @Form\Attributes({"method":"post"})
 * @Form\Options({"prefer_form_input_filter": true})
 */
class ExistingOperatorLicence
{
    /**
     * @Form\Name("existingOperatorLicence")
     * @Form\Options({
     *     "label": "user-registration.field.existing-operator-licence.label",
     *     "hint": "user-registration.field.existing-operator-licence.hint",
     *     "value_options":{"N":"select-option-no", "Y":"select-option-yes"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"}
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"existingOperatorLicence", "placeholder":"", "required":false})
     * @Form\Type("Radio")
     */
    public $existingOperatorLicence = null;
}
