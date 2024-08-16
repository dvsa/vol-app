<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\EnableConversationsPopupActions;
use Olcs\Form\Model\Fieldset\EnableConversationsPopupText;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("enable_conversations")
 * @Form\Attributes({"method": "post"})
 * @Form\Type(\Common\Form\Form::class)
 * @Form\Options({"prefer_form_input_filter": true})
 */
class EnableConversationsPopup
{
    /**
     * @Form\Name("form-text")
     * @Form\ComposedObject(\Olcs\Form\Model\Fieldset\EnableConversationsPopupText::class)
     */
    public ?EnableConversationsPopupText $text = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject(\Olcs\Form\Model\Fieldset\EnableConversationsPopupActions::class)
     */
    public ?EnableConversationsPopupActions $formActions = null;
}
