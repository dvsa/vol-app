<?php

namespace Olcs\Form\Model\Form\Surrender;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("surrender-review")
 * @Form\Type("\Common\Form\Form")
 */
class Review
{
    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\SaveButton")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions = null;
}
