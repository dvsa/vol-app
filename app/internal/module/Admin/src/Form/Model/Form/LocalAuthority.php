<?php

namespace Admin\Form\Model\Form;

use Olcs\Form\Model\Fieldset\Base;
use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("LocalAuthority")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class LocalAuthority extends Base
{
    /**
     * @Form\Name("localAuthorityDetails")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\LocalAuthorityDetails")
     */
    public $localAuthorityDetails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OkCancelFormActions")
     */
    public $formActions = null;
}
