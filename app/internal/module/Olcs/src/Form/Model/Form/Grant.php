<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("Grant")
 * @Form\Options({"label":""})
 * @Form\Attributes({"method":"post"})
 */
class Grant
{
    public const FIELD_GRANT_AUTHORITY = 'grant-authority';

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
     * @Form\Name(\Olcs\Form\Model\Form\Grant::FIELD_GRANT_AUTHORITY)
     * @Form\Required(true)
     * @Form\Type("Hidden")
     */
    public $grantAuthority;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\GrantFormActions")
     */
    public $formActions = null;
}
