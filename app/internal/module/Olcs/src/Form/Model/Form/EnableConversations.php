<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\EnableConversationsActions;
use Olcs\Form\Model\Fieldset\EnableConversationsText;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("enable_conversations")
 * @Form\Attributes({"method": "post", "class": "table__form"})
 * @Form\Type(\Common\Form\Form::class)
 * @Form\Options({"prefer_form_input_filter": true})
 */
class EnableConversations
{
    /**
     * @Form\Name("form-text")
     * @Form\ComposedObject(\Olcs\Form\Model\Fieldset\EnableConversationsText::class)
     */
    public ?EnableConversationsText $text = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject(\Olcs\Form\Model\Fieldset\EnableConversationsActions::class)
     */
    public ?EnableConversationsActions $formActions = null;
}
