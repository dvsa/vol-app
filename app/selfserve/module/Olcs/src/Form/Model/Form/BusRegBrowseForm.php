<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("bus-reg-browse-form")
 * @Form\Attributes({"method":"post", "action":""})
 * @Form\Options({"prefer_form_input_filter": true})
 */
class BusRegBrowseForm
{
    /**
     * @Form\Name("fields")
     * @Form\Options({"label":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\BusRegBrowse")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\BusRegBrowseButtons")
     */
    public $formActions = null;
}
