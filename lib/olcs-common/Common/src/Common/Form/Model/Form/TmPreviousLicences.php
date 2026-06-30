<?php

namespace Common\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("tm-previous-licences")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "action_lcfirst": true})
 */
class TmPreviousLicences
{
    /**
     * @Form\Name("tm-previous-licences-details")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TransportManager\PreviousLicencesDetails")
     */
    public $tmPreviousLicencesDetails;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormCrudActions")
     */
    public $formActions;
}
