<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\DisableConversationsActions;
use Olcs\Form\Model\Fieldset\DisableConversationsText;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("disable_conversations")
 * @Form\Attributes({"method": "post", "class": "table__form"})
 * @Form\Type(\Common\Form\Form::class)
 * @Form\Options({"prefer_form_input_filter": true})
 */
class DisableConversations
{
    /**
     * @Form\Name("form-text")
     * @Form\ComposedObject(\Olcs\Form\Model\Fieldset\DisableConversationsText::class)
     */
    public ?DisableConversationsText $text = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject(\Olcs\Form\Model\Fieldset\DisableConversationsActions::class)
     */
    public ?DisableConversationsActions $formActions = null;
}
