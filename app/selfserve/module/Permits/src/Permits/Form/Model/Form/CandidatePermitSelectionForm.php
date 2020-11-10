<?php

namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("CandidatePermitSelection")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class CandidatePermitSelectionForm
{
    /**
     * @Form\Name("fields")
     * @Form\Type("Zend\Form\Fieldset")
     */
    public $fieldset = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\SaveAndReturnOnly")
     */
    public $submit = null;
}
