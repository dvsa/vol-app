<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-psv-discs")
 * @Form\Attributes({"method":"post", "class":"table__form"})
 * @Form\Type("Common\Form\Form")
 */
class PsvDiscs
{
    /**
     * @Form\Name("table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\TableRequired")
     */
    public $table;

    /**
     * @Form\Name("form-actions")
     * @Form\Type("Laminas\Form\Fieldset")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
