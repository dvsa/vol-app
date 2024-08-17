<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("OperatorRepresentation")
 * @Form\Attributes({"method":"post"})
 * @Form\Options({"prefer_form_input_filter": true})
 */
class OperatorRepresentation
{
    /**
     * @Form\Name("actingOnOperatorsBehalf")
     * @Form\Options({
     *     "label": "user-registration.field.act-on-operators-behalf.label",
     *     "hint": "user-registration.field.act-on-operators-behalf.hint",
     *     "value_options":{"N":"select-option-no", "Y":"select-option-yes"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"}
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"actingOnOperatorsBehalf", "placeholder":"", "required":false})
     * @Form\Type("Radio")
     */
    public $actingOnOperatorsBehalf = null;
}
