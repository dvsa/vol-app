<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("impounding")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class Impounding
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $case = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Name("application_details")
     * @Form\Options({"label":"Application details"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ApplicationDetails")
     */
    public $applicationDetails = null;

    /**
     * @Form\Name("hearing")
     * @Form\Options({"label":"Hearing","id":"hearing_fieldset"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\Hearing")
     */
    public $hearing = null;

    /**
     * @Form\Name("outcome")
     * @Form\Options({"label":"Outcome"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\Outcome")
     */
    public $outcome = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
