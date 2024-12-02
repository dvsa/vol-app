<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("AgreeTerms")
 * @Form\Attributes({"method":"post","label":"AgreeTerms"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "label": "AgreeTerms"})
 */
class AgreeTerms
{
    /**
     * @Form\Name("main")
     * @Form\Options({"label":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\AgreeTerms")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ContinueOrSignOut")
     */
    public $formActions = null;
}
