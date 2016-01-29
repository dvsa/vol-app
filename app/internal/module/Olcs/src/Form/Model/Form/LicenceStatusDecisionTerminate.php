<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("LicenceStatusDecisionTerminate")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class LicenceStatusDecisionTerminate
{
    /**
     * @Form\Name("licence-decision")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\LicenceStatusDecisionTerminate")
     */
    public $terminate = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ConfirmFormActions")
     */
    public $formActions = null;
}
