<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("qualification")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "action_lcfirst": true})
 */
class Qualification
{
    /**
     * @Form\Name("qualification-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\QualificationDetails")
     */
    public $qualificationDetails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\QualificationActions")
     */
    public $formActions = null;
}
