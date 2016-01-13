<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("transport-manager-application-or-licence-full")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class TransportManagerApplicationOrLicenceFull
{
    /**
     * @Form\Name("details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\TmResponsibilities")
     */
    public $details = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\SaveButtons")
     * @Form\Attributes({"class":"actions-container"})
     */
    public $formActions = null;
}
