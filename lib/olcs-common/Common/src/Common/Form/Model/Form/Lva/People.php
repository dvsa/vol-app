<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-people")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class People
{
    /**
     * @Form\Name("table")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TableRequiredPeople")
     */
    public $table;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
