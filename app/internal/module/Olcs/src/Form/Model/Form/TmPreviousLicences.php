<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

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
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\TmPreviousLicencesDetails")
     */
    public $tmPreviousLicencesDetails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
