<?php

namespace Olcs\Form\Model\Form\Lva;

use Zend\Form\Annotation as Form;

/**
 * Form to show signature information from GDS Verify and continue to pay application fees
 *
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class ApplicationSigned
{
    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Lva\Fieldset\ApplicationSignatureDetails")
     */
    public $signatureDetails = null;
}
