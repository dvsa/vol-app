<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("Grant")
 * @Form\Options({"label":""})
 * @Form\Attributes({"method":"post"})
 */
class Grant
{
    /**
     * @Form\Name("messages")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Messages")
     */
    public $messages;

    /**
     * @Form\Name("inspection-request-confirm")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\InspectionRequestConfirm")
     */
    public $inspectionRequestConfirm;

    /**
     * @Form\Name("inspection-request-grant-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\InspectionRequestGrantDetails")
     */
    public $inspectionRequestGrantDetails;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\GrantFormActions")
     */
    public $formActions = null;
}
