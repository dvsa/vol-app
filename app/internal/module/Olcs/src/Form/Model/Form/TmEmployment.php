<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("employment")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "action_lcfirst": true})
 */
class TmEmployment
{
    /**
     * @Form\Name("tm-employer-name-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\TmEmployerNameDetails")
     */
    public $tmEmployerNameDetails = null;

    /**
     * @Form\Name("address")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Address")
     * @Form\Options({"label":"internal.transport-manager.employment.form.address"})
     */
    public $address = null;

    /**
     * @Form\Name("tm-employment-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\TmEmploymentDetails")
     */
    public $tmEmploymentDetails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
