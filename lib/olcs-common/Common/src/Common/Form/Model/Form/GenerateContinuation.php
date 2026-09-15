<?php

namespace Common\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("continuation")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class GenerateContinuation
{
    /**
     * @Form\Name("details")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\GenerateContinuationDetails")
     */
    public $details;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\GenerateContinuationFormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
