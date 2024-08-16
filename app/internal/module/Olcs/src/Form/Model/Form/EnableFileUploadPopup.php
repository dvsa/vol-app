<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\EnableFileUploadPopupText;
use Olcs\Form\Model\Fieldset\EnableFileUploadPopupActions;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("enable_attachments")
 * @Form\Attributes({"method": "post"})
 * @Form\Type(\Common\Form\Form::class)
 * @Form\Options({"prefer_form_input_filter": true})
 */
class EnableFileUploadPopup
{
    /**
     * @Form\Name("form-text")
     * @Form\ComposedObject(\Olcs\Form\Model\Fieldset\EnableFileUploadPopupText::class)
     */
    public ?EnableFileUploadPopupText $text = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject(\Olcs\Form\Model\Fieldset\EnableFileUploadPopupActions::class)
     */
    public ?EnableFileUploadPopupActions $formActions = null;
}
