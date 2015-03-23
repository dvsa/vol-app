<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("LicenceStatusDecisionCurtail")
 * @Form\Attributes({"method":"post"})
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
     * @Form\Name("licence-decision-curtail")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\LicenceStatusDecisionCurtail")
     */
    public $curtail = null;

    /**
     * @Form\Name("licence-decision-curtail-now")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\LicenceStatusDecisionCurtailNow")
     */
    public $curtailNow = null;
}