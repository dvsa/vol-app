<?php

namespace Admin\Form\Model\Form;

use Olcs\Form\Model\Fieldset\Base;
use Laminas\Form\Annotation as Form;
use Laminas\Form\FormInterface;

/**
 * @Form\Name("system-info-message")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class SystemInfoMessage extends Base
{
    /**
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\SystemInfoMessageDetails")
     */
    public $details = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
