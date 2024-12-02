<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("AgreeTerms")
 * @Form\Attributes({"method":"post","label":"operator-registration.form.labe"})
 * @Form\Options({"prefer_form_input_filter": true, "label": ""})
 */
class AgreeTerms
{
    /**
     * @Form\Attributes({"id": "termsAgreed", "placeholder": ""})
     * @Form\Options({
     *     "label": "user-registration.field.termsAgreed.label",
     *     "label_attributes" : {
     *         "class":"form-control form-control--checkbox form-control--confirm"
     *     },
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     *     "must_be_value": "Y"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $termsAgreed = null;
}
