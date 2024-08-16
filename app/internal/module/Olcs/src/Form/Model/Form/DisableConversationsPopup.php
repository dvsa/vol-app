<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\DisableConversationsPopupActions;
use Olcs\Form\Model\Fieldset\DisableConversationsPopupText;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("disable_conversations")
 * @Form\Attributes({"method": "post"})
 * @Form\Type(\Common\Form\Form::class)
 * @Form\Options({"prefer_form_input_filter": true})
 */
class DisableConversationsPopup
{
    /**
     * @Form\Name("form-text")
     * @Form\ComposedObject(\Olcs\Form\Model\Fieldset\DisableConversationsPopupText::class)
     */
    public ?DisableConversationsPopupText $text = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject(\Olcs\Form\Model\Fieldset\DisableConversationsPopupActions::class)
     */
    public ?DisableConversationsPopupActions $formActions = null;
}
