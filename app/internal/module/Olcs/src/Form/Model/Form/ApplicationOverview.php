<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("application-overview")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class ApplicationOverview
{
    /**
     * @Form\Name("details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ApplicationOverviewDetails")
     * @Form\Options({"label": "Details"})
     */
    public $details = null;

    /**
     * @Form\Name("tracking")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ApplicationTracking")
     * @Form\Options({"label": "Tracking"})
     * @Form\Attributes({"class": "tracking"})
     */
    public $tracking = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     */
    public $formActions = null;
}
