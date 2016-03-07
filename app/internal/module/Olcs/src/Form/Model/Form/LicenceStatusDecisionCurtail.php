<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("LicenceStatusDecisionCurtail")
 * @Form\Attributes({"method":"post", "class":"status-decision-form"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class LicenceStatusDecisionCurtail
{
    /**
     * @Form\Name("licence-decision-affect-immediate")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\LicenceStatusDecisionAffectImmediate")
     */
    public $affectImmediate = null;

    /**
     * @Form\Name("licence-decision")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\LicenceStatusDecisionCurtail")
     */
    public $curtail = null;

    /**
     * @Form\Name("licence-decision-reasons")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\LicenceStatusDecisionReasons")
     */
    public $reasons = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\LicenceStatusDecisionFormActions")
     */
    public $formActions = null;
}
