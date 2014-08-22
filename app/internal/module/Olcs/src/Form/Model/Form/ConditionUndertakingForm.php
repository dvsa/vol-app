<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("condition-undertaking-form")
 * @Form\Attributes({"method":"post"})
 */
class ConditionUndertakingForm
{

    /**
     * @Form\Name("condition-undertaking")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ConditionUndertaking")
     */
    public $conditionUndertaking = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ConditionUndertakingFormFormActions")
     */
    public $formActions = null;


}

