<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * Form to submit SAML authentication request to GDS Verify
 *
 * @Form\Attributes({"method":"post", "id":"initiate-request"})
 * @Form\Type("Common\Form\Form")
 */
class VerifyRequest
{
    /**
     * @Form\Type("Hidden")
     */
    public $SAMLRequest = null;

    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Lva\Fieldset\VerifyRequestDetails")
     */
    public $details = null;

    /**
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\SubmitButton")
     */
    public $formActions = null;
}
